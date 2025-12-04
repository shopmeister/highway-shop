<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Redgecko\Magnalister\Controller;

use Doctrine\DBAL\Connection;
use League\Flysystem\Filesystem;
use ML;
use MLFilesystem;
use MLHttp;
use MLMessage;
use MLOrder;
use MLSetting;
use Redgecko\Magnalister\Service\MagnalisterSalesChannelContextFactory;
use Shopware\Core\Checkout\Document\Service\DocumentGenerator;
use Shopware\Core\Checkout\Order\SalesChannel\OrderService;
use Shopware\Core\Content\Media\Core\Application\AbstractMediaPathStrategy;
use Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator;
use Shopware\Core\Content\Media\Core\Application\MediaLocationBuilder;
use Shopware\Core\Content\Product\Stock\OrderStockSubscriber;
use Shopware\Core\Content\Product\Stock\StockStorage;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Context\AdminApiSource;
use Shopware\Core\Framework\Context\Exception\InvalidContextSourceException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryForwardCompatibilityDecorator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\PluginLifecycleService;
use Shopware\Core\Framework\Plugin\PluginService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Twig\TemplateFinder;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Kernel;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\User\UserEntity;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Cache\CacheResponseSubscriber;
use Shopware\Storefront\Framework\Csrf\CsrfPlaceholderHandler;
use Shopware\Storefront\Framework\Twig\Extension\CsrfFunctionExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionFactory;
use Symfony\Component\Routing\Annotation\Route;


#[Route(defaults: ['_routeScope' => ['storefront']])]
#[Package('storefront')]
class MagnalisterController extends AbstractController {

    /**
     * @var string|null
     */
    private static $localeId;

    /**
     * @var NumberRangeValueGeneratorInterface
     */
    private static $numberRangeValueGenerator;
    private static Filesystem $fileSystem;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var SessionFactory
     */
    private $session;

    /**
     * @var Connection
     */
    static private $connection;

    /**
     * @var RequestStack
     */
    static private $requestStack;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var ContainerInterface
     */
    static private $shopwareContainer;

    /**
     * @var Context
     */
    static private $context;

    /**
     * //CSRF has been removed from Shopware 6.5
     * @var CsrfTokenManagerInterface
     */
    //static private $csrfTokenManager;

    /**
     * @var StateMachineRegistry
     */
    static private $stateMachineRegistry;

    /**
     * @var string|null language id of current admin user
     */
    static private $languageId;

    /**
     * @var string user id of current admin user
     */
    static private $adminUserId;

    /**
     * @var Redgecko\Magnalister\Service\MagnalisterSalesChannelContextFactory
     */
    static private $salesChannelContextFactory;

    /**
     * @var mixed Media URL generator (supports different Shopware versions) Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator|Shopware\Core\Content\Media\Infrastructure\Path\MediaUrlGenerator
     */
    static private $mediaConverter;

    /**
     * @var use Shopware\Core\Content\Media\Core\Application\AbstractMediaPathStrategy;
     */
    static private $mediaPathStrategy;

    /**
     * @var use Shopware\Core\Content\Media\Core\Application\MediaLocationBuilder;
     */
    static private $mediaLocationBuilder;

    /**
     * @var OrderStockSubscriber
     */
    static private $stockUpdater;


    /**
     * @var StockStorage
     */
    static private $StockStorage;

    /**
     * @var OrderService
     */
    static private $orderService;

    /**
     * @var DocumentGenerator
     */
    static private $documentService;

    /**
     * @var PluginService
     */
    static private $pluginService;

    /**
     * @var PluginLifecycleService
     */
    static private $pluginLifecycleService;

    public function __construct(
        ParameterBagInterface                 $params,
        SessionFactory                        $session,
        Connection                            $connection,
        RequestStack                          $requestStack,
        Kernel                                $kernel,
        ContainerInterface                    $shopwareContainer,
        //CsrfTokenManagerInterface             $csrfTokenManager,
        StateMachineRegistry                  $stateMachineRegistry,
        NumberRangeValueGeneratorInterface    $numberRangeValueGenerator,
        MagnalisterSalesChannelContextFactory $salesChannelContextFactory,
                                              $mediaConverter,//Use generic type to support different Shopware versions
        AbstractMediaPathStrategy             $mediaPathStrategy,
        MediaLocationBuilder                  $mediaLocationBuilder,
        Filesystem                            $fileSystemPublic,
                                              $stockUpdater,//plugins can decorate this service, it will provide an EventSubscriberInterface in that case
                                              $StockStorage,//if pickware erp install it can has different type
        OrderService                          $orderService,
        DocumentGenerator                     $documentService,
        PluginService                         $pluginService,
        PluginLifecycleService                $pluginLifecycleService
    ) {
        self::$salesChannelContextFactory = $salesChannelContextFactory;
        self::$mediaConverter = $mediaConverter;
        self::$mediaPathStrategy = $mediaPathStrategy;
        self::$mediaLocationBuilder = $mediaLocationBuilder;
        self::$fileSystem = $fileSystemPublic;
        $this->params = $params;
        $this->session = $session;
        self::$connection = $connection;
        self::$requestStack = $requestStack;
        $this->kernel = $kernel;
        self::$shopwareContainer = $shopwareContainer;
        self::$context = Context::createDefaultContext();
        //self::$csrfTokenManager = $csrfTokenManager;
        self::$stateMachineRegistry = $stateMachineRegistry;
        self::$numberRangeValueGenerator = $numberRangeValueGenerator;
        self::$stockUpdater = $stockUpdater;
        self::$StockStorage = $StockStorage;
        self::$orderService = $orderService;
        self::$documentService = $documentService;
        self::$pluginService = $pluginService;
        self::$pluginLifecycleService = $pluginLifecycleService;
        $this->prepareWritablePaths();

        try {
            define("MLSHOPWAREREVERSION", $this->params->get('kernel.shopware_version_revision'));
            $version = $this->params->get('kernel.shopware_version');
            if (strpos($version, "-")) {
                //removing additional character such following version
                //'6.5.0.0-rc1' rc1 is problematic
                $version = substr($version, 0, strpos($version, "-"));
            }
            define("MLSHOPWAREVERSION", $version);
        } catch (\Exception $oEx) {
        }
    }

    /**
     * @return SalesChannelContextFactory
     */
    public static function getSalesChannelContextFactory(): MagnalisterSalesChannelContextFactory {
        return self::$salesChannelContextFactory;
    }

    /**
     * @return mixed Media URL generator (supports different Shopware versions) Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator|Shopware\Core\Content\Media\Infrastructure\Path\MediaUrlGenerator
     */
    public static function getMediaConverter() {
        return self::$mediaConverter;
    }

    /**
     * @return AbstractMediaPathStrategy
     */
    public static function getMediaPathStrategy(): AbstractMediaPathStrategy {
        return self::$mediaPathStrategy;
    }
    /**
     * @return MediaLocationBuilder
     */
    public static function getMediaLocationBuilder(): MediaLocationBuilder {
        return self::$mediaLocationBuilder;
    }

    /**
     * @return Filesystem
     */
    public static function getFileSystem(): Filesystem {
        return self::$fileSystem;
    }


    /**
     * @param string $key
     * @param string $userId
     * @param Request $request
     * @param Context $context
     * @param RequestDataBag $requestDataBag
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'magnalister/{key}/{userId}', name: 'magnalister.admin.page', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['storefront'],'XmlHttpRequest' => true])]
    public function loadMagnalister(string $key, string $userId, Request $request, Context $context, RequestDataBag $requestDataBag): Response {

        try {

            $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');

            require_once($appPath.'../vendor/magnalister/MagnalisterFunctions.php');

            $response = new Response();

            $this->setLanguageLocaleUserId($userId);
            $this->validateMagnalisterSession($key);

            $sShopwareReversion = $this->params->get('kernel.shopware_version_revision');
            $_PluginPath = $this->getPluginPath($appPath);
            $debugPrint = '';

            if (file_exists($_PluginPath)) {
                require_once($_PluginPath);

                $this->restoreIndividualProgrammings($appPath);

                $output = ML::gi()->run();

                $sClientVersion = MLSetting::gi()->get('sClientBuild');

                $MLCss = '';
                $MLJs = '';
                foreach (array_unique(MLSetting::gi()->get('aCss')) as $sFile) {//echo $sFile;
                    try {
                        $MLCss .= '
                        <link rel="stylesheet" type="text/css" href="' . MLHttp::gi()->getResourceUrl('css_' . $sFile) . '?' . $sClientVersion . '">';
                    } catch (\Exception $ex) {
                        if (MLSetting::gi()->blDebug) {
                            MLMessage::gi()->addDebug($ex);
                        }
                    }
                }

                $MLJs .= '
                <script type="text/javascript">
                    var shopware_version = "'.$sShopwareReversion.'/";
                </script>';

                foreach (array_unique(MLSetting::gi()->get('aJs')) as $sFile) {
                    try {

                        $MLJs .= '
                    <script src="' . MLHttp::gi()->getResourceUrl('js_' . $sFile) . '?' . $sClientVersion . '" type="text/javascript"></script>';
                    } catch (\Exception $ex) {
                        if (MLSetting::gi()->blDebug) {
                            MLMessage::gi()->addDebug($ex);
                        }
                    }
                }
                $MLBodyClass = implode(' ', MLSetting::gi()->get('aBodyClasses'));
            }

            header('Content-Type: text/html; charset=utf-8');

            echo('<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <title>magnalister Admin</title>
                    <style>body { font: 12px/1.3em sans-serif; }</style>
                    ' . $MLCss . '
                    ' . $MLJs . '
                </head>
                <body class="' . $MLBodyClass . '">
                    '.$output.'
                    <pre>'.$debugPrint.'</pre> 
                </body>
            </html>
        ');
            \MagnalisterFunctions::stop();

        } catch (\Throwable $ex) {
            echo($this->getMessageHtml((isset(self::getShopwareRequest()->query->all()['ml-debug']) || (class_exists('MLSetting') && MLSetting::gi()->blDebug)) ?
                $ex->getMessage().'<br>'.
                $ex->getFile().'<br>'.
                $ex->getLine().'<pre>'.
                $ex->getTraceAsString().'</pre>' :
                'An error occurs, please contact <a href="http://www.magnalister.com/kontakt" class="ml-js-noBlockUi" target="_blank">magnalister-Service</a>
                '));
            \MagnalisterFunctions::stop();
        }
        return $response;//we cannot use symfony response for magnalister, there is some cases that javascript code will minmized from Shopware core and that made a problem for javascript code of magnalister
    }

    /**
     * Normalizes the given URL by replacing consecutive slashes with a single slash,
     * except for the protocol section (http:// or https://).
     *
     * @param string $url The URL to be normalized.
     * @return string The normalized URL.
     */


    protected function getMessageHtml($sMessage) {
        return '<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <title>magnalister exception</title>
                </head>
                <body>
                    <div class="installation-failed" style="position: fixed;height: 100%;width: 100%;">
                        <div class="if-wrap" style="position: relative;top: 50%;-webkit-transform: translateY(-50%);-moz-transform: translateY(-50%);-ms-transform: translateY(-50%);-o-transform: translateY(-50%);transform: translateY(-50%);width: fit-content;margin: 0 auto;">
                            <p class="if-text" style="font-size: 20px;text-align: center;">
                                '.$sMessage.'
                            </p>
                        </div>
                    </div>
                </body>
            </html>';
    }

    /**
     * @param Request $request
     * @param Context $context
     * @param RequestDataBag $requestDataBag
     * @return Response
     * @throws \Exception
     */
    #[Route(path: '/magnalister', name: 'magnalister.front.page', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['storefront'], 'XmlHttpRequest' => true])]
    public function loadFrontMagnalister(Request $request, Context $context, RequestDataBag $requestDataBag): Response {

        if ($request->query->has('ml') !== null && isset($request->query->all()['ml']['do'])) {

        } else {
            throw new \Exception('No parameters given for cronjob.');
        }
        header('Content-Type: text/html; charset=utf-8');
        self::$languageId = Defaults::LANGUAGE_SYSTEM;
        $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');
        $_PluginPath = $this->getPluginPath($appPath);
        $response = new Response();
        ob_start();
        require_once($_PluginPath);
        $this->restoreIndividualProgrammings($appPath);
        ML::gi()->runFrontend('do');
        \MagnalisterFunctions::stop();
        return $response;
    }


    #[Route(path: 'api/magnalister/delete_session/{keyId}', name: 'magnalister.delete-session', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api'], 'auth_required' => false])]
    public function deleteMagnalisterSession(string $keyId, RequestDataBag $dataBag, Context $context): JsonResponse {
        /** @var EntityRepository $oKeyRepository */
        $oKeyRepository = self::$shopwareContainer->get('magnalister_shopware6.repository');

        $oKeyRepository->delete([['id' => $keyId]], self::$context);
        $oKeyEntities = $oKeyRepository
            ->search((new Criteria())
                ->addFilter(new EqualsFilter('id', $keyId)), self::$context)
            ->getEntities();

        if ($oKeyEntities->last() === null) {
            return new JsonResponse($keyId, 200);
        } else {
            return new JsonResponse('error', 402);
        }
    }


    #[Route(path: 'api/magnalister/add_session', name: 'magnalister.add-session', methods: ['POST'], defaults: ['_routeScope' => ['api'], 'auth_required' => false])]
    public function addMagnalisterSession(RequestDataBag $dataBag, Context $context): JsonResponse {
        /** @var EntityRepository $oKeyRepository */
        $oKeyRepository = self::$shopwareContainer->get('magnalister_shopware6.repository');

        $key = Uuid::randomHex();
        $ip = $this->getClientIP();
        $browser = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $data = [
            'key'     => $key,
            'ip'      => $ip,
            'browser' => $browser
        ];
        $oKeyRepository->create([$data], self::$context);


        $oKeyEntities = $oKeyRepository
            ->search((new Criteria())
                ->addFilter(new EqualsFilter('key', $key)), self::$context)
            ->getEntities();

        if ($oKeyEntities->last() !== null) {
            return new JsonResponse($oKeyEntities->last()->getId(), 200);
        } else {
            return new JsonResponse('error', 402);
        }

    }




    #[Route(path: 'api/magnalister/reset_session', name: 'magnalister.reset-session', methods: ['POST'], defaults: ['_routeScope' => ['api'], 'auth_required' => false])]
    public function resetMagnalisterSession(RequestDataBag $dataBag, Context $context): JsonResponse {
        /** @var EntityRepository $oKeyRepository */
        $oKeyRepository = self::$shopwareContainer->get('magnalister_shopware6.repository');
        $key = Uuid::randomHex();
        $data = [
            'id'      => $dataBag->get('keyId'),
            'key'     => $key,
            'ip'      => $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']),
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        $oKeyRepository->upsert([$data], self::$context);

        $oKeyEntities = $oKeyRepository
            ->search((new Criteria())
                ->addFilter(new EqualsFilter('key', $key)), self::$context)
            ->getEntities();

        if ($oKeyEntities->last() !== null) {
            return new JsonResponse($oKeyEntities->last()->getId(), 200);
        } else {
            return new JsonResponse('error', 402);
        }
    }

    #[Route(path: 'store-api/v{version}/_action/magnalister/fetch_order', name: 'api.action.magnalister.order-fetch', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    #[Route(path: 'api/_action/magnalister/fetch_order', name: 'api.action.magnalister.order-fetch', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    public function fetchOrder(RequestDataBag $dataBag, Context $context): JsonResponse {

        $result = $this->getHistory($dataBag->get('magnalister_order_id'), $dataBag->get('user_id'));
        if (!empty($result)) {
            return new JsonResponse(['orderContent' => $result['Content'], 'logo' => $result['Logo']], 200);
        } else {
            return new JsonResponse(['orderContent' => '', 'logo' => ''], 200);
        }
    }

    #[Route(path: 'api/v{version}/_action/magnalister/fetch_logo', name: 'api.action.magnalister.order-logo', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    #[Route(path: 'api/_action/magnalister/fetch_logo', name: 'api.action.magnalister.order-logo', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    public function fetchLogo(RequestDataBag $dataBag, Context $context): JsonResponse {

        $result = $this->getOrderLogo($dataBag->get('magnalister_order_id'), $dataBag->get('user_id'));
        return new JsonResponse(['logo' => $result['Logo']], 200);
    }


    #[Route(path: 'api/v{version}/_action/magnalister/additional_order', name: 'api.action.magnalister.additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    #[Route(path: 'api/_action/magnalister/additional_order', name: 'api.action.magnalister.additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    public function storeReturnCarierAndReturnTrakingCode(RequestDataBag $dataBag, Context $context): JsonResponse {

        $result = $this->setAdditional($dataBag->get('magnalister_return_carrier'), $dataBag->get('magnalister_return_tracking_code'), $dataBag->get('magnalister_order_id'), $dataBag->get('user_id'));
        return new JsonResponse(['Successfull' => $result['Successfull']], 200);
    }


    #[Route(path: 'api/v{version}/_action/magnalister/store_amazon_additional_order', name: 'api.action.magnalister.store-amazon-additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    #[Route(path: 'api/_action/magnalister/store_amazon_additional_order', name: 'api.action.magnalister.store-amazon-additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    public function storeCarrierCodeAndShippingMethod(RequestDataBag $dataBag, Context $context): JsonResponse {

        $result = $this->setAmazonAdditional($dataBag->get('magnalister_carrier_code'), $dataBag->get('magnalister_shipping_method'), $dataBag->get('magnalister_order_id'), $dataBag->get('user_id'));
        return new JsonResponse(['Successfull' => $result['Successfull']], 200);
    }


    #[Route(path: 'api/v{version}/_action/magnalister/fetch_additional_order', name: 'api.action.magnalister.fetch-additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    #[Route(path: 'api/_action/magnalister/fetch_additional_order', name: 'api.action.magnalister.fetch-additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    public function fetchReturnCarierAndReturnTrakingCode(RequestDataBag $dataBag, Context $context): JsonResponse {

        $result = $this->fetchAdditional($dataBag->get('magnalister_order_id'), $dataBag->get('user_id'));
        return new JsonResponse(['Additional' => $result['Additional'], 'Marketplace' => $result['Marketplace']], 200);
    }


    #[Route(path: 'api/v{version}/_action/magnalister/fetch_amazon_additional_order', name: 'api.action.magnalister.fetch-amazon-additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    #[Route(path: 'api/_action/magnalister/fetch_amazon_additional_order', name: 'api.action.magnalister.fetch-amazon-additional-order', methods: ['GET', 'POST'], defaults: ['_routeScope' => ['api']])]
    public function fetchCarierCodeAndShippingMethod(RequestDataBag $dataBag, Context $context): JsonResponse {

        $result = $this->fetchAmazonAdditional($dataBag->get('magnalister_order_id'), $dataBag->get('user_id'));
        return new JsonResponse(['Additional' => $result['Additional'], 'Marketplace' => $result['Marketplace']], 200);
    }

    private function getHistory(string $OrderId, string $userId): array {
        /**
         * @var $oUser UserEntity
         */
        $this->setLanguageLocaleUserId($userId);

        $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');
        $_PluginPath = $this->getPluginPath($appPath);
        require_once $_PluginPath;

        MLOrder::factory()->set('current_orders_id', $OrderId);

        if (!ML::isInstalled()) {
            throw new \Exception('magnalister is not installed');
        }
        $hasTransactionId = (int)self::$connection->executeQuery(
            'SELECT COUNT(*) FROM `magnalister_orders` WHERE `current_orders_id` = :orders_id ', [
                'orders_id' => $OrderId,
            ]
        )->fetchOne();


        if (!empty($hasTransactionId)) {
            ML::setFastLoad(true);


            $oOrder = MLOrder::factory()->set('current_orders_id', $OrderId);

            $sTitleHtml = $oOrder->getTitle();
            $string = '';

            if ($oOrder->get('special') !== null) {
                ob_start();
                require(MLFilesystem::gi()->getViewPath('hook_orderdetails'));
                $string = ob_get_clean();
            }

            return array('Content' => $string, 'Logo' => $sTitleHtml);
        } else {
            return array();
        }
    }

    private function getOrderLogo(string $OrderId, string $userId): array {
        /**
         * @var $oUser UserEntity
         */
        $this->setLanguageLocaleUserId($userId);

        $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');
        $_PluginPath = $this->getPluginPath($appPath);
        require_once $_PluginPath;

        $AllmagnalisterOrders = json_decode($OrderId, true);
        $sTitleHtml[] = array();

        foreach ($AllmagnalisterOrders as $value) {
            MLOrder::factory()->set('current_orders_id', $value);

            if (!ML::isInstalled()) {
                throw new \Exception('magnalister is not installed');
            }

            $hasTransactionId = (int)self::$connection->executeQuery(
                'SELECT COUNT(*) FROM `magnalister_orders` WHERE `current_orders_id` = :orders_id ', [
                    'orders_id' => $value,
                ]
            )->fetchOne();

            if (!empty($hasTransactionId)) {
                ML::setFastLoad(true);
                $oOrder = MLOrder::factory()->set('current_orders_id', $value);
                $sTitleHtml[] = array('id' => $value, 'logo' => $oOrder->getLogo());
            }
        }
        return array('Logo' => $sTitleHtml);
    }

    private function setAdditional(string $returnCarrier, string $returnTrackingNumber, string $OrderId, string $userId): array {
        /**
         * @var $oUser UserEntity
         */
        $this->setLanguageLocaleUserId($userId);

        $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');
        $_PluginPath = $this->getPluginPath($appPath);
        require_once $_PluginPath;

        MLOrder::factory()->set('current_orders_id', $OrderId);

        if (!ML::isInstalled()) {
            throw new \Exception('magnalister is not installed');
        }
        $jasonData = json_encode(array('returnCarrier' => $returnCarrier, 'returnTrackingNumber' => $returnTrackingNumber));
        $queryUpdate = 'UPDATE magnalister_orders SET shopAdditionalOrderField=:shopAdditionalOrderField WHERE `current_orders_id` = :orders_id ';
        try {
            $hasTransactionId = self::$connection->executeQuery(
                $queryUpdate, ['shopAdditionalOrderField' => (string)$jasonData, 'orders_id' => $OrderId]
            );
        } catch (\Throwable $th) {
            $hasTransactionId = self::$connection->executeQuery(
                $queryUpdate, ['shopAdditionalOrderField' => (string)$jasonData, 'orders_id' => $OrderId]
            );

        }
        return array('Successfull' => true);
    }

    private function fetchAdditional(string $OrderId, string $userId): array {
        /**
         * @var $oUser UserEntity
         */
        $this->setLanguageLocaleUserId($userId);

        $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');
        $_PluginPath = $this->getPluginPath($appPath);
        require_once $_PluginPath;

        MLOrder::factory()->set('current_orders_id', $OrderId);

        if (!ML::isInstalled()) {
            throw new \Exception('magnalister is not installed');
        }
        $hasTransactionId = self::$connection->executeQuery(
            'SELECT shopAdditionalOrderField FROM `magnalister_orders` WHERE `current_orders_id` = :orders_id ', [
                'orders_id' => $OrderId,
            ]
        )->fetchOne();

        $MarketplaceName = self::$connection->executeQuery(
            'SELECT platform FROM `magnalister_orders` WHERE `current_orders_id` = :orders_id ', [
                'orders_id' => $OrderId,
            ]
        )->fetchOne();

        return array('Additional' => $hasTransactionId, 'Marketplace' => $MarketplaceName);
    }

    private function fetchAmazonAdditional(string $OrderId, string $userId): array {
        /**
         * @var $oUser UserEntity
         */
        $this->setLanguageLocaleUserId($userId);

        $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');
        $_PluginPath = $this->getPluginPath($appPath);
        require_once $_PluginPath;

        MLOrder::factory()->set('current_orders_id', $OrderId);

        if (!ML::isInstalled()) {
            throw new \Exception('magnalister is not installed');
        }
        $hasTransactionId = self::$connection->executeQuery(
            'SELECT shopAdditionalOrderField FROM `magnalister_orders` WHERE `current_orders_id` = :orders_id ', [
                'orders_id' => $OrderId,
            ]
        )->fetchOne();

        $MarketplaceName = self::$connection->executeQuery(
            'SELECT platform FROM `magnalister_orders` WHERE `current_orders_id` = :orders_id ', [
                'orders_id' => $OrderId,
            ]
        )->fetchOne();

        return array('Additional' => $hasTransactionId, 'Marketplace' => $MarketplaceName);
    }

    private function setAmazonAdditional(string $carrierCode, string $shipMethod, string $OrderId, string $userId): array {
        /**
         * @var $oUser UserEntity
         */
        $this->setLanguageLocaleUserId($userId);

        $appPath = $this->container->get('kernel')->locateResource('@RedMagnalisterSW6');
        $_PluginPath = $this->getPluginPath($appPath);
        require_once $_PluginPath;

        MLOrder::factory()->set('current_orders_id', $OrderId);

        if (!ML::isInstalled()) {
            throw new \Exception('magnalister is not installed');
        }
        $jasonData = json_encode(array('carrierCode' => $carrierCode, 'shipMethod' => $shipMethod));
        $queryUpdate = 'UPDATE magnalister_orders SET shopAdditionalOrderField=:shopAdditionalOrderField WHERE `current_orders_id` = :orders_id ';
        try {
            $hasTransactionId = self::$connection->executeQuery(
                $queryUpdate, ['shopAdditionalOrderField' => (string)$jasonData, 'orders_id' => $OrderId]
            );
        } catch (\Throwable $th) {
            $hasTransactionId = self::$connection->executeUpdate(
                $queryUpdate, ['shopAdditionalOrderField' => (string)$jasonData, 'orders_id' => $OrderId]
            );

        }
        return array('Successfull' => true);
    }

    /**
     * @return Connection
     */
    public static function getShopwareConnection(): Connection {
        return self::$connection;
    }

    /**
     * @return Request|null
     *
     */
    public static function getShopwareRequest() {
        return self::$requestStack->getMainRequest();
    }

    /**
     * @return ContainerInterface
     */
    public static function getShopwareMyContainer(): ContainerInterface {
        return self::$shopwareContainer;
    }

    /**
     * csrfTokenManager
     * @return CsrfTokenManagerInterface
     */
    /*public static function getShopwareCsrfTokenManager(): CsrfTokenManagerInterface {
        return self::$csrfTokenManager;
    }*/

    /**
     * @return StateMachineRegistry
     */
    public static function getShopwareStateMachineRegistry(): StateMachineRegistry {
        return self::$stateMachineRegistry;
    }

    /**
     * @return string
     */
    public static function getShopwareLanguageId(): ?string {
        return self::$languageId;
    }

    /**
     * @return string
     */
    public static function getShopwareLocaleId(): ?string {
        return self::$localeId;
    }

    /**
     * @return string
     */
    public static function getShopwareUserId(): ?string {
        return self::$adminUserId;
    }

    /**
     * @return NumberRangeValueGeneratorInterface
     */
    public static function getNumberRangeValueGenerator(): NumberRangeValueGeneratorInterface {
        return self::$numberRangeValueGenerator;
    }

    /**
     * @return OrderStockSubscriber
     */
    public static function getStockUpdater(): object {
        return self::$stockUpdater;
    }

    /**
     * @return StockStorage;
     */
    public static function getStockStorage(): StockStorage {
        return self::$StockStorage;
    }

    /**
     * @return OrderService
     */
    public static function getOrderService(): OrderService {
        return self::$orderService;
    }

    /**
     * @return DocumentGenerator
     */
    public static function getDocumentService(): DocumentGenerator {
        return self::$documentService;
    }


    /**
     * @return PluginLifecycleService
     */
    public static function getPluginLifecycleService() {
        $pluginName = self::$pluginService->getPluginByName('RedMagnalisterSW6', self::$context);
        //$plugin = $this->pluginService->getPluginByName($pluginName, self::$context);
        self::$pluginLifecycleService->updatePlugin($pluginName, self::$context);

        return $pluginName;
    }

    /**
     * @return \Shopware\Core\Framework\Plugin\PluginEntity
     */
    public static function getPluginServiceByName($sName) {
        return self::$pluginService->getPluginByName($sName, self::$context);
    }

    private function prepareWritablePaths() {
        $sCachePath = $this->kernel->getCacheDir().DIRECTORY_SEPARATOR.'RedMagnalisterSW6'.DIRECTORY_SEPARATOR;
        $sLogPath = $this->kernel->getLogDir().DIRECTORY_SEPARATOR.'RedMagnalisterSW6'.DIRECTORY_SEPARATOR;
        $sGeneralWritablePath = $this->kernel->getCacheDir().DIRECTORY_SEPARATOR;
        if (!is_dir($sCachePath)) {
            mkdir($sCachePath);
        }
        if (is_dir($sCachePath)) {
            define('MAGNALISTER_CACHE_DIRECTORY', $sCachePath);
        }
        if (!is_dir($sLogPath)) {
            mkdir($sLogPath);
        }
        if (is_dir($sLogPath)) {
            define('MAGNALISTER_LOG_DIRECTORY', $sLogPath);
        }

        if (is_dir($sGeneralWritablePath)) {
            define('MAGNALISTER_WRITABLE_DIRECTORY', $sGeneralWritablePath);
        }
    }

    /**
     * This give back the option for individual programmings
     *  Store them into log folder /var/log/RedMagnalisterSW6/10_Cutomer
     *      this script will copy it to ML Codepool directory
     *
     * @throws \ML_Core_Exception_Update
     */
    private function restoreIndividualProgrammings($appPath) {
        $_PluginPath = $appPath.'..'.DIRECTORY_SEPARATOR.'Lib'.DIRECTORY_SEPARATOR;

        foreach (array('00_Dev', '10_Customer') as $directory) {
            if (file_exists(MAGNALISTER_LOG_DIRECTORY.$directory) && !file_exists($_PluginPath.'Codepool'.DIRECTORY_SEPARATOR.$directory)) {


                \MLHelper::getFilesystemInstance()->cp(MAGNALISTER_LOG_DIRECTORY . $directory, $_PluginPath . 'Codepool' . DIRECTORY_SEPARATOR . $directory);
            }
        }
    }

    protected function getPluginPath($appPath): string {
        define('MAGNALISTER_VENDOR_DIRECTORY', $this->container->get('kernel')->locateResource('@RedMagnalisterSW6').'../vendor/');
        if (file_exists($appPath.'../Lib/Core/ML.php')) {
            $_PluginPath = $appPath.'../Lib/Core/ML.php';
        } else {
            throw new \Exception('magnalister Lib directroy doesn\'t exist.');
        }
        return $_PluginPath;
    }

    protected function validateMagnalisterSession(string $key): void {
        $oSearchCriteria = new Criteria();
        $oSearchCriteria->addFilter(new EqualsFilter('id', $key));
        $oSearchCriteria->addFilter(new EqualsFilter('browser', $_SERVER['HTTP_USER_AGENT']));
        //The IP condition is not suitable for identifying the user, as many servers don't provide the client IP to PHP.
        //$oSearchCriteria->addFilter(new EqualsFilter('ip', $this->getClientIP(),));

        //To keep generated key secure as much as possible every key will be expired after one hour
        $oSearchCriteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new RangeFilter('createdAt', [
                        RangeFilter::GT => (new \DateTime())->modify('-5 minutes')->format(Defaults::STORAGE_DATE_TIME_FORMAT)
                    ]),
                    new RangeFilter('updatedAt', [
                        RangeFilter::GT => (new \DateTime())->modify('-5 minutes')->format(Defaults::STORAGE_DATE_TIME_FORMAT)
                    ]),
                ]
            )
        );
        $oKeyRepository = self::$shopwareContainer->get('magnalister_shopware6.repository')->search($oSearchCriteria, self::$context)->getEntities();
        /**
         * @var EntityRepositoryInterface $oKeyRepository
         */
        if ($oKeyRepository->last() === null) {
            echo($this->getMessageHtml('Try to open magnalister from admin menu of Shopware'));
            \MagnalisterFunctions::stop();
        }
    }

    protected function setLanguageLocaleUserId(string $userId): void {
        /**
         * @var $oUser UserEntity
         */
        $oUser = MagnalisterController::getShopwareMyContainer()->get('user.repository')
            ->search((new Criteria())->addFilter(new EqualsFilter('id', $userId)), Context::createDefaultContext())->getEntities()->last();
        if ($oUser !== null) {
            self::$localeId = $oUser->getLocaleId();
            self::$adminUserId = $userId;
        } else {
            throw new \Exception('Please open magnalister plugin again via Shopware admin menu');
        }

        $oCriteria = new Criteria();
        $oCriteria->addFilter(new EqualsFilter('localeId', self::$localeId));
        $oLanguage = MagnalisterController::getShopwareMyContainer()->get('language.repository')->search($oCriteria, Context::createDefaultContext())->getEntities()->last();
        if (!is_object($oLanguage)) {
            $oLanguage = MagnalisterController::getShopwareMyContainer()->get('language.repository')->search(new Criteria(), Context::createDefaultContext())->getEntities()->last();
        }
        if ($oLanguage !== null) {
            self::$languageId = $oLanguage->getId();
        }
    }

    protected function getClientIP(): string {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']);
        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip)[0];
        }
        return $ip;
    }
}
