<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */


namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\AfterRenderHtmlEvent;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Doctrine\DBAL\Connection;
use Exception;
use Dde\Picqer\Barcode\BarcodeGeneratorJPG;
use RuntimeException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Dde\Endroid\QrCode\Color\Color;
use Dde\Endroid\QrCode\Encoding\Encoding;
use Dde\Endroid\QrCode\QrCode;
use Dde\Endroid\QrCode\Writer\PngWriter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\BeforeRenderTwigTemplateEvent;
use Twig\TwigFunction;

/**
 * Class TwigRenderer
 */
class TwigRenderer
{

    private array $templateData;

    public function __construct(
        private readonly TwigEnvironmentFactory             $twigEnvironmentFactory,
        private readonly SeoUrlPlaceholderHandlerInterface  $seoUrlPlaceholderHandler,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly Connection                         $connection,
        private readonly Logger                             $logger,
        private readonly ?EventDispatcherInterface          $dispatcher = null
    )
    {
        $this->templateData = [];
    }

    /**
     * @return array
     */
    private function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * @param array $templateData
     */
    private function setTemplateData(array $templateData): void
    {
        $this->templateData = $templateData;
    }

    public function renderHtml($twigTemplate, $templateData): string
    {
        try {
            $this->setTemplateData($templateData);

            // Optimize twig template
            $this->logger->logExecutionDuration(function () use (&$twigTemplate) {
                $event = new BeforeRenderTwigTemplateEvent($twigTemplate);
                $this->dispatcher?->dispatch($event);
                $twigTemplate = $event->getTwigTemplate();
            }, "Twig template optimization duration: %s ms");

            // Render twig template
            $html = '';
            $this->logger->logExecutionDuration(function () use ($twigTemplate, $templateData, &$html) {
                $twigEnvironment = $this->twigEnvironmentFactory->createTwigEnvironment();
                $twigEnvironment->addFunction(new TwigFunction('dde_qr_code', $this->renderQrCode(...)));
                $twigEnvironment->addFunction(new TwigFunction('dde_barcode', $this->renderBarcode(...)));
                $twig = $twigEnvironment->createTemplate($twigTemplate);
                $html = $twig->render($templateData);
            }, "Twig template rendering duration: %s ms");

            // Optimize html
            $this->logger->logExecutionDuration(function () use ($twigTemplate, $templateData, &$html) {
                $this->resolveSeoUrls($html, $templateData);
                if ($this->dispatcher) {
                    $event = new AfterRenderHtmlEvent($html, $templateData['document'] ?? null, $templateData['context'] ?? null);
                    $this->dispatcher->dispatch($event);
                    $html = $event->getHtml();
                }
            }, "Html optimization duration: %s ms");

        } catch (Exception $e) {
            throw new RuntimeException(
                "An error occurred during the rendering process! Please create a support ticket in your Shopware Account and provide the following data: <br>" .
                "<br>" .
                "<strong>Error message:</strong><br>"
                . "<div>" . $e->getMessage() . "</div><br>"
//                . "<strong>HTML:</strong><br>"
//                . "<pre>" . htmlspecialchars($html) . "</pre><br>"
                . "<strong>Template:</strong><br>"
                . "<pre>" . htmlspecialchars($twigTemplate) . "</pre><br>"
                . "<strong>Data:</strong><br>"
                . "<pre>" . htmlspecialchars(json_encode($templateData, JSON_PRETTY_PRINT)) . "</pre><br>"
            );
        }

        return $html;
    }

    protected function renderQrCode(?string $data, ?int $width): string
    {
        try {
            if (is_null($data)) throw new Exception('No data - Please add a condition to the qr-code to only render it if data is available.');
            $width = $width ?? 300;
            $this->resolveSeoUrls($data, $this->getTemplateData());

            $writer = new PngWriter();
            $qrCode = QrCode::create($data)
                ->setEncoding(new Encoding('UTF-8'))
                ->setSize($width)
                ->setMargin(0)
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));
            return 'data:image/png;base64,' . base64_encode($writer->write($qrCode)->getString());
        } catch (\Throwable $e) {
            $reflect = new \ReflectionClass($e);
            $textImageRenderer = new TextImageRenderer();
            return 'data:image/jpg;base64,' . base64_encode($textImageRenderer->renderTextImage($reflect->getShortName() . ": " . $e->getMessage()));
        }
    }

    protected function renderBarcode(?string $data, ?string $type): string
    {
        try {
            if (is_null($data)) throw new Exception('No data - Please add a condition to the barcode to only render it if data is available.');
            $type = ($type !== 'null' ? $type : null) ?? 'C39';
            $generator = new BarcodeGeneratorJPG();
            return 'data:image/jpg;base64,' . base64_encode($generator->getBarcode($data, $type, 1, 30));
        } catch (\Throwable $e) {
            $reflect = new \ReflectionClass($e);
            $textImageRenderer = new TextImageRenderer();
            return 'data:image/jpg;base64,' . base64_encode($textImageRenderer->renderTextImage($reflect->getShortName() . ": " . $e->getMessage()));
        }
    }

    /**
     * @param $html
     * @param $templateData
     * @return void
     *
     * Resolve seo url placeholders for e.g. product urls.
     * SEO url placeholders can be added via Twig using the seoUrl function.
     *
     * {% set productId = lineItem.product ? (lineItem.product.id ?? null) : null %}
     * {% if productId %}
     *     {% set seoUrl = seoUrl("frontend.detail.page", { productId: productId }) %}
     * {% else %}
     *     {% set seoUrl = null %}
     * {% endif %}
     * @throws \Doctrine\DBAL\Exception
     */
    public function resolveSeoUrls(&$html, $templateData): void
    {
        if ((!isset($templateData['order']) || !($templateData['order'] instanceof OrderEntity) || !$templateData['order']->getSalesChannelId()) && (!isset($templateData['salesChannelId']) || !isset($templateData['languageId']))) return;
        /** @var OrderEntity $order */
        $order = $templateData['order'];
        if ($templateData['order'] instanceof OrderEntity) {
            $salesChannelId = $order->getSalesChannelId() ?? $templateData['salesChannelId'];
            $languageId = $order->getLanguageId() ?? $templateData['languageId'];
        } else {
            $salesChannelId = $templateData['salesChannelId'];
            $languageId = $templateData['languageId'];
        }
        $salesChannelContext = $this->createSalesChannelContext($salesChannelId);
        if (!$salesChannelContext) return;
        $url = $this->getSalesChannelDomainUrl($salesChannelId, $languageId);
        if (!$url) return;
        $html = $this->seoUrlPlaceholderHandler->replace($html, $url, $salesChannelContext);
    }

    private function createSalesChannelContext($salesChannelId): SalesChannelContext
    {
        return $this->salesChannelContextFactory->create(Uuid::randomHex(), $salesChannelId);
    }

    /**
     * @param string $salesChannelId
     * @param string $languageId
     * @return string|null
     * @throws \Doctrine\DBAL\Exception
     */
    private function getSalesChannelDomainUrl(string $salesChannelId, string $languageId): ?string
    {
        $url = $this->connection->fetchOne(
            "SELECT `url` FROM `sales_channel_domain` WHERE LOWER(HEX(sales_channel_id)) = :sales_channel_id 
                 AND LOWER(HEX(language_id)) = :language_id ORDER BY `url` DESC LIMIT 1;",
            ['sales_channel_id' => $salesChannelId, 'language_id' => $languageId]
        );
        return $url ?: null;
    }

}
