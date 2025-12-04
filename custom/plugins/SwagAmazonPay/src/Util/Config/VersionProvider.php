<?php declare(strict_types=1);

namespace Swag\AmazonPay\Util\Config;

use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;

readonly class VersionProvider implements VersionProviderInterface
{

    public function __construct(
        private string $shopwareVersion,
        private EntityRepository $paymentMethodRepository
    )
    {
    }

    public function getVersions(?Context $context = null): array
    {
        $context = $context ?? Context::createDefaultContext();
        return [
            'shopware' => $this->shopwareVersion,
            'plugin' => $this->getPluginVersion($context),
        ];
    }

    private function getPluginVersion(Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addAssociation('plugin');
        $criteria->addFilter(new EqualsFilter('id', PaymentMethodInstaller::AMAZON_PAYMENT_ID));

        /** @var PaymentMethodEntity $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->search($criteria, $context)->first();

        /** @var PluginEntity $plugin */
        $plugin = $paymentMethod->getPlugin();

        return $plugin->getVersion();
    }
}
