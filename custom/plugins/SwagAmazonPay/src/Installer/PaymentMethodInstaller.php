<?php declare(strict_types=1);

namespace Swag\AmazonPay\Installer;

use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Swag\AmazonPay\Components\PaymentHandler\AmazonPaymentHandler;
use Swag\AmazonPay\SwagAmazonPay;

readonly class PaymentMethodInstaller implements InstallerInterface
{
    public const AMAZON_PAYMENT_ID = 'f7b88fc9c0104702a96f664dabfe2656';
    public const AMAZON_PAY_LOGO_MEDIA_ID = '3471f3e6eabd11ee943cb7ee51f34817';

    private const PAYMENT_METHODS = [
        [
            'id' => self::AMAZON_PAYMENT_ID,
            'handlerIdentifier' => AmazonPaymentHandler::class,
            'position' => -110,
            'name' => 'Amazon Pay',
            'technicalName' => 'amazon_pay',
            'translations' => [
                'de-DE' => [
                    'name' => 'Amazon Pay',
                    'description' => 'Mit Amazon Pay bezahlen',
                ],
                'en-GB' => [
                    'name' => 'Amazon Pay',
                    'description' => 'Pay with Amazon Pay',
                ],
            ],
        ],
    ];

    public function __construct(
        private EntityRepository $paymentMethodRepository,
        private PluginIdProvider $pluginIdProvider,
        private FileSaver        $fileSaver,
        private EntityRepository $mediaRepository
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->upsertPaymentMethod($paymentMethod, $context->getContext());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->setPaymentMethodStatus($paymentMethod, false, $context->getContext());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->setPaymentMethodStatus($paymentMethod, false, $context->getContext());
        }
    }

    public function update(UpdateContext $context): void
    {
        $this->paymentMethodRepository->upsert([[
            'id' => self::AMAZON_PAYMENT_ID,
            'technicalName' => self::PAYMENT_METHODS[0]['technicalName'],
        ]], $context->getContext());
    }

    public function activate(ActivateContext $context): void
    {
        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->setPaymentMethodStatus($paymentMethod, true, $context->getContext());
        }
    }

    private function upsertPaymentMethod(array $paymentMethod, Context $context): void
    {
        $pluginId = $this->pluginIdProvider->getPluginIdByBaseClass(SwagAmazonPay::class, $context);
        $paymentMethod['pluginId'] = $pluginId;

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($paymentMethod): void {
            $existing = $this->paymentMethodRepository->search(new Criteria([self::AMAZON_PAYMENT_ID]), $context)->first();
            /** @var PaymentMethodEntity $existing */
            if ($existing) {
                if (!$existing->getMediaId()) {
                    $mediaEntity = $this->mediaRepository->search(new Criteria([self::AMAZON_PAY_LOGO_MEDIA_ID]), $context)->first();
                    if (!$mediaEntity) {
                        $this->mediaRepository->create([[
                            'id' => self::AMAZON_PAY_LOGO_MEDIA_ID,
                            'title' => 'Amazon Pay Logo',
                        ]], $context);
                    }
                    $path = __DIR__ . '/../Resources/public/storefront/img/amazon_pay.svg';
                    $mediaFile = new MediaFile(
                        $path,
                        'image/svg',
                        'svg',
                        filesize($path)
                    );
                    $this->fileSaver->persistFileToMedia(
                        $mediaFile,
                        'amazon_pay_logo',
                        self::AMAZON_PAY_LOGO_MEDIA_ID,
                        $context
                    );
                    $paymentMethod['mediaId'] = self::AMAZON_PAY_LOGO_MEDIA_ID;
                }
            }


            $this->paymentMethodRepository->upsert([$paymentMethod], $context);
        });
    }

    private function setPaymentMethodStatus(array $paymentMethod, bool $active, Context $context): void
    {
        $paymentMethodCriteria = new Criteria([$paymentMethod['id']]);
        $hasPaymentMethod = $this->paymentMethodRepository->searchIds($paymentMethodCriteria, $context)->getTotal() > 0;

        if (!$hasPaymentMethod) {
            return;
        }

        $data = [
            'id' => $paymentMethod['id'],
            'active' => $active,
        ];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($data): void {
            $this->paymentMethodRepository->upsert([$data], $context);
        });
    }
}
