<?php declare(strict_types=1);

namespace Swag\AmazonPay;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyDefinition;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use Shopware\Core\System\Salutation\SalutationDefinition;
use Swag\AmazonPay\Installer\CustomFieldsInstaller;
use Swag\AmazonPay\Installer\DatabaseInstaller;
use Swag\AmazonPay\Installer\DefaultSalutationInstaller;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Swag\AmazonPay\Installer\RuleInstaller;
use Swag\AmazonPay\Uninstaller\ConfigurationUninstaller;
use Swag\AmazonPay\Uninstaller\LogUninstaller;
use Swag\AmazonPay\Util\SwagAmazonPayClassLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

if (\file_exists(__DIR__ . '/../vendor/autoload.php')) {
    (new SwagAmazonPayClassLoader())->register();
}

class SwagAmazonPay extends Plugin
{
    public const CHECKOUT_SESSION_KEY = 'swag-amazon-pay.checkout-session-id';

    public function install(InstallContext $installContext): void
    {
        $this->writeDefaultInheritance();
        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        $paymentMethodRepository = $this->getRepository($this->container, PaymentMethodDefinition::ENTITY_NAME);
        /** @var FileSaver $fileSaver */
        $fileSaver = $this->container->get(FileSaver::class);
        $mediaRepository = $this->getRepository($this->container, MediaDefinition::ENTITY_NAME);
        $customFieldSetRepository = $this->getRepository($this->container, CustomFieldSetDefinition::ENTITY_NAME);
        $salutationRepository = $this->getRepository($this->container, SalutationDefinition::ENTITY_NAME);
        $ruleRepository = $this->getRepository($this->container, RuleDefinition::ENTITY_NAME);
        $currencyRepository = $this->getRepository($this->container, CurrencyDefinition::ENTITY_NAME);

        (new PaymentMethodInstaller($paymentMethodRepository, $pluginIdProvider, $fileSaver, $mediaRepository))->install($installContext);
        (new CustomFieldsInstaller($customFieldSetRepository))->install($installContext);
        (new DefaultSalutationInstaller($salutationRepository))->install($installContext);
        (new RuleInstaller($ruleRepository, $currencyRepository, $paymentMethodRepository))->install($installContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        $this->writeDefaultInheritance();
        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        /** @var FileSaver $fileSaver */
        $fileSaver = $this->container->get(FileSaver::class);
        $ruleRepository = $this->getRepository($this->container, RuleDefinition::ENTITY_NAME);
        $currencyRepository = $this->getRepository($this->container, CurrencyDefinition::ENTITY_NAME);
        $paymentMethodRepository = $this->getRepository($this->container, PaymentMethodDefinition::ENTITY_NAME);
        $mediaRepository = $this->getRepository($this->container, MediaDefinition::ENTITY_NAME);

        (new PaymentMethodInstaller($paymentMethodRepository, $pluginIdProvider, $fileSaver, $mediaRepository))->update($updateContext);
        (new RuleInstaller($ruleRepository, $currencyRepository, $paymentMethodRepository))->update($updateContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        $paymentMethodRepository = $this->getRepository($this->container, PaymentMethodDefinition::ENTITY_NAME);
        /** @var FileSaver $fileSaver */
        $fileSaver = $this->container->get(FileSaver::class);
        $mediaRepository = $this->getRepository($this->container, MediaDefinition::ENTITY_NAME);
        $customFieldSetRepository = $this->getRepository($this->container, CustomFieldSetDefinition::ENTITY_NAME);

        (new PaymentMethodInstaller($paymentMethodRepository, $pluginIdProvider, $fileSaver, $mediaRepository))->deactivate($deactivateContext);
        (new CustomFieldsInstaller($customFieldSetRepository))->deactivate($deactivateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        $paymentMethodRepository = $this->getRepository($this->container, PaymentMethodDefinition::ENTITY_NAME);
        /** @var FileSaver $fileSaver */
        $fileSaver = $this->container->get(FileSaver::class);
        $mediaRepository = $this->getRepository($this->container, MediaDefinition::ENTITY_NAME);
        $customFieldSetRepository = $this->getRepository($this->container, CustomFieldSetDefinition::ENTITY_NAME);
        $ruleRepository = $this->getRepository($this->container, RuleDefinition::ENTITY_NAME);
        $currencyRepository = $this->getRepository($this->container, CurrencyDefinition::ENTITY_NAME);

        (new PaymentMethodInstaller($paymentMethodRepository, $pluginIdProvider, $fileSaver, $mediaRepository))->uninstall($uninstallContext);
        (new CustomFieldsInstaller($customFieldSetRepository))->uninstall($uninstallContext);
        (new DatabaseInstaller($connection))->uninstall($uninstallContext);
        (new RuleInstaller($ruleRepository, $currencyRepository, $paymentMethodRepository))->uninstall($uninstallContext);
        (new ConfigurationUninstaller($connection))->uninstall($uninstallContext);

        $logsDir = $this->container->getParameter('kernel.logs_dir');

        (new LogUninstaller($logsDir))->uninstall($uninstallContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        $paymentRepository = $this->getRepository($this->container, PaymentMethodDefinition::ENTITY_NAME);
        /** @var FileSaver $fileSaver */
        $fileSaver = $this->container->get(FileSaver::class);
        $mediaRepository = $this->getRepository($this->container, MediaDefinition::ENTITY_NAME);
        $customFieldSetRepository = $this->getRepository($this->container, CustomFieldSetDefinition::ENTITY_NAME);

        (new PaymentMethodInstaller($paymentRepository, $pluginIdProvider, $fileSaver, $mediaRepository))->activate($activateContext);
        (new CustomFieldsInstaller($customFieldSetRepository))->activate($activateContext);
    }

    private function writeDefaultInheritance()
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $result = $connection->executeQuery("SELECT * FROM `system_config` WHERE `configuration_key` LIKE 'SwagAmazonPay.settings.%'");
        $hasExistingInheritanceFlag = false;
        $hasExistingConfiguration = $result->rowCount() > 0;
        if ($hasExistingConfiguration) {
            $result = $connection->executeQuery("SELECT * FROM `system_config` WHERE `configuration_key` LIKE 'SwagAmazonPay.settings.noInheritance' AND sales_channel_id IS NULL");
            $hasExistingInheritanceFlag = $result->rowCount() > 0;
        }
        if(!$hasExistingInheritanceFlag) {
            $connection->insert('system_config', [
                'id' => Uuid::randomBytes(),
                'configuration_key' => 'SwagAmazonPay.settings.noInheritance',
                'configuration_value' => '{"_value":' . ($hasExistingConfiguration ? 'false' : 'true') . '}', //existing configuration vs. fresh installation
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function enrichPrivileges(): array
    {
        return [
            'order.viewer' => [
                'swag_amazon_pay_payment_notification:read',
            ],
        ];
    }

    private function getRepository(ContainerInterface $container, string $entityName): EntityRepository
    {
        $repository = $container->get(\sprintf('%s.repository', $entityName), ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if (!$repository instanceof EntityRepository) {
            throw new ServiceNotFoundException(\sprintf('%s.repository', $entityName));
        }

        return $repository;
    }
}
