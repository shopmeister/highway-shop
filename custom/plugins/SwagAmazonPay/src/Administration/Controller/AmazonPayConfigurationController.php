<?php declare(strict_types=1);

namespace Swag\AmazonPay\Administration\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Storefront\Framework\Routing\Router;
use Swag\AmazonPay\Administration\Controller\Exception\DisallowedPathException;
use Swag\AmazonPay\Administration\Controller\Exception\PluginConfigurationImportException;
use Swag\AmazonPay\Administration\Controller\Exception\PluginConfigurationImportVersionMismatchException;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Util\Config\PluginConfigurationExporterInterface;
use Swag\AmazonPay\Util\Config\PluginConfigurationImporterInterface;
use Swag\AmazonPay\Util\Connection\ConnectionInspectorInterface;
use Swag\AmazonPay\Util\Helper\AmazonPayPaymentMethodHelper;
use Swag\AmazonPay\Util\Logging\LogFileProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
class AmazonPayConfigurationController extends AbstractController
{
    private ConnectionInspectorInterface $connectionInspector;

    private LoggerInterface $logger;

    private PluginConfigurationExporterInterface $pluginConfigurationExporter;

    private RouterInterface $router;

    private PluginConfigurationImporterInterface $pluginConfigurationImporter;

    private LogFileProviderInterface $logFileProvider;

    private AmazonPayPaymentMethodHelper $amazonPayPaymentMethodHelper;

    public function __construct(
        ConnectionInspectorInterface         $connectionInspector,
        LoggerInterface                      $logger,
        PluginConfigurationExporterInterface $pluginConfigurationExporter,
        PluginConfigurationImporterInterface $pluginConfigurationImporter,
        RouterInterface                      $router,
        LogFileProviderInterface             $logFileProvider,
        AmazonPayPaymentMethodHelper         $amazonPayPaymentMethodHelper
    )
    {
        $this->connectionInspector = $connectionInspector;
        $this->logger = $logger;
        $this->pluginConfigurationExporter = $pluginConfigurationExporter;
        $this->router = $router;
        $this->pluginConfigurationImporter = $pluginConfigurationImporter;
        $this->logFileProvider = $logFileProvider;
        $this->amazonPayPaymentMethodHelper = $amazonPayPaymentMethodHelper;
    }

    #[Route(path: 'api/_action/swag_amazon_pay_configuration/inspect-connection', name: 'api.action.swag.amazon.pay.validate.credentials', defaults: ['_acl' => ['swag_amazonpay.viewer']], methods: ['POST'])]
    public function inspectConnection(RequestDataBag $dataBag): Response
    {
        $credentials = $this->extractCredentialsFromRequest($dataBag);

        $response = $this->connectionInspector->test($credentials);

        if ($response['success'] === false) {
            $this->logger->error('Error while testing Api credentials', $response);
        } else {
            $this->logger->info('API credentials successfully validated');
        }

        return new JsonResponse($response);
    }

    #[Route(path: 'api/_action/swag_amazon_pay_configuration/get-ipn-url', name: 'api.action.swag.amazon.pay.get.ipn.url', defaults: ['_acl' => ['swag_amazonpay.viewer']], methods: ['POST', 'GET'])]
    public function getIpnUrl(): Response
    {
        $url = $this->router->generate(
            'api.action.swag_amazon_pay.ipn',
            [],
            Router::ABSOLUTE_URL
        );

        $response = [
            'url' => $url,
        ];

        return new JsonResponse($response);
    }

    #[Route(path: 'api/_action/swag_amazon_pay_configuration/export-config', name: 'api.action.swag.amazon.pay.export.config', defaults: ['_acl' => ['swag_amazonpay.viewer']], methods: ['POST'])]
    public function exportConfig(Context $context): JsonResponse
    {
        $config = $this->pluginConfigurationExporter->export($context);

        return new JsonResponse($config);
    }

    /**
     * @throws PluginConfigurationImportException
     */

    #[Route(path: 'api/_action/swag_amazon_pay_configuration/import-config/{ignoreVersions}', name: 'api.action.swag.amazon.pay.import.config', defaults: ['_acl' => ['swag_amazonpay.viewer'], 'ignoreVersions' => false], methods: ['POST'])]
    public function importConfig(Request $request, Context $context): JsonResponse
    {
        $ignoreVersions = \filter_var($request->request->get('ignoreVersions'), \FILTER_VALIDATE_BOOLEAN);

        /** @var string $content */
        $content = $request->getContent();
        $config = \json_decode($content, true);

        if (!$config) {
            throw RoutingException::missingRequestParameter('content');
        }

        try {
            $this->pluginConfigurationImporter->import($config, $context, $ignoreVersions);

            return new JsonResponse();
        } catch (PluginConfigurationImportVersionMismatchException $ex) {
            return new JsonResponse($ex->getVersions());
        } catch (\Exception $ex) {
            throw new PluginConfigurationImportException($ex);
        }
    }

    #[Route(path: 'api/_action/swag_amazon_pay_configuration/log-files', name: 'api.action.swag.amazon.pay.logfiles', defaults: ['_acl' => ['swag_amazonpay.viewer']], methods: ['GET'])]
    public function getLogFiles(): JsonResponse
    {
        $fileList = $this->logFileProvider->getLogFileList();

        return new JsonResponse($fileList);
    }

    /**
     * Returns the generated archive path as response.
     */
    #[Route(path: 'api/_action/swag_amazon_pay_configuration/generate-log-archive', name: 'api.action.swag.amazon.pay.generate.log.archive', defaults: ['_acl' => ['swag_amazonpay.viewer']], methods: ['POST'])]
    public function generateLogArchive(Request $request): Response
    {
        /** @var array $files */
        $files = $request->request->all()['files'] ?? [];

        if (empty($files)) {
            throw RoutingException::missingRequestParameter('files');
        }

        $this->logFileProvider->cleanupLogArchives();
        $zipArchivePath = $this->logFileProvider->compressLogFiles($files);

        return new Response($zipArchivePath);
    }

    /**
     * Returns the generated archive contents as response.
     */
    #[Route(path: 'api/_action/swag_amazon_pay_configuration/download-log-archive', name: 'api.action.swag.amazon.pay.download.log.archive', defaults: ['_acl' => ['swag_amazonpay.viewer']], methods: ['GET'])]
    public function downloadLogArchive(Request $request): Response
    {
        $path = (string)$request->query->get('path');

        if (!$path) {
            throw RoutingException::missingRequestParameter('path');
        }

        $pathExpression = "/^swag-amazon-pay\/[a-z0-9]*.zip$/";
        if (!\preg_match($pathExpression, $path)) {
            throw new DisallowedPathException($path);
        }

        $response = new Response($this->logFileProvider->getCompressedLogArchive($path));

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            \basename($path),
            // only printable ascii
            (string)\preg_replace('/[\x00-\x1F\x7F-\xFF]/', '_', \basename($path))
        );

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Sets AmazonPay as the default PaymentMethod for the selected SalesChannel.
     */
    #[Route(path: 'api/_action/swag_amazon_pay_configuration/saleschannel-default', name: 'api.action.swag_amazon_pay_configuration.saleschannel_default', defaults: ['_acl' => ['swag_amazonpay.viewer']], methods: ['POST'])]
    public function salesChannelDefault(Request $request, Context $context): Response
    {
        $salesChannelId = $request->request->get('salesChannelId');
        if (!\is_string($salesChannelId)) {
            $salesChannelId = null;
        }

        $this->amazonPayPaymentMethodHelper->setSalesChannelDefault(
            $salesChannelId,
            $context
        );

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function extractCredentialsFromRequest(RequestDataBag $dataBag): array
    {
        $credentials = [];

        foreach ($dataBag->all() as $key => $value) {
            if (\array_key_exists($key, ConfigServiceInterface::DEFAULT_CONFIG)) {
                $credentials[$key] = $value;
            }
        }

        return $credentials;
    }
}
