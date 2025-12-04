<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl;

use Doctrine\DBAL\Connection;
use Monolog\Logger;
use Pickware\ApiErrorHandlingBundle\PickwareApiErrorHandlingBundle;
use Pickware\BundleInstaller\BundleInstaller;
use Pickware\DalBundle\PickwareDalBundle;
use Pickware\DebugBundle\PickwareDebugBundle;
use Pickware\DocumentBundle\PickwareDocumentBundle;
use Pickware\FeatureFlagBundle\PickwareFeatureFlagBundle;
use Pickware\InstallationLibrary\DependencyAwareTableDropper;
use Pickware\InstallationLibrary\PluginLifecycleErrorRecovery;
use Pickware\MoneyBundle\PickwareMoneyBundle;
use Pickware\PickwareDhl\Config\DhlConfig;
use Pickware\PickwareDhl\Installation\PickwareDhlInstaller;
use Pickware\ShippingBundle\Carrier\CarrierAdapterRegistryCompilerPass;
use Pickware\ShippingBundle\PickwareShippingBundle;
use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Migration\MigrationCollectionLoader;
use Shopware\Core\Framework\Migration\MigrationRuntime;
use Shopware\Core\Framework\Migration\MigrationSource;
use Shopware\Core\Framework\Parameter\AdditionalBundleParameters;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Struct\Collection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

if (file_exists(__DIR__ . '/../vendor/pickware/dependency-loader/src/DependencyLoader.php')) {
    require_once __DIR__ . '/../vendor/pickware/dependency-loader/src/DependencyLoader.php';
}

class PickwareDhl extends Plugin
{
    /**
     * @var class-string<Bundle>[]
     */
    private const ADDITIONAL_BUNDLES = [
        PickwareDalBundle::class,
        PickwareDocumentBundle::class,
        PickwareMoneyBundle::class,
        PickwareApiErrorHandlingBundle::class,
        PickwareShippingBundle::class,
        PickwareDebugBundle::class,
        PickwareFeatureFlagBundle::class,
    ];

    public const CARRIER_TECHNICAL_NAME_DHL = 'dhl';

    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        // Ensure the bundle classes can be loaded via auto-loading.
        if (isset($GLOBALS['PICKWARE_DEPENDENCY_LOADER'])) {
            $kernelParameters = $parameters->getKernelParameters();
            // This method is only called with the kernel parameters when invoked by the Kernel.
            // As of Shopware version >= 6.6.1.1, a new behavior was introduced where the Storefront theme is recompiled
            // whenever a new plugin is installed or updated (see Shopware\Storefront\Theme\Subscriber\PluginLifecycleSubscriber).
            // However, during this process, the `getAdditionalBundles` method of each installed bundle is called
            // without the kernel parameters because the call does not pass through the Kernel (see
            // Shopware\StorefrontPluginConfiguration/StorefrontPluginConfigurationFactory.php and
            // Shopware\Storefront\Theme\Subscriber\PluginLifecycleSubscriber.php).
            // Therefore, we can safely ignore this call, as the necessary data is already loaded by the Kernel.
            if (array_key_exists('kernel.plugin_infos', $kernelParameters) && array_key_exists('kernel.project_dir', $kernelParameters)) {
                $GLOBALS['PICKWARE_DEPENDENCY_LOADER']->ensureLatestDependenciesOfPluginsLoaded(
                    $kernelParameters['kernel.plugin_infos'],
                    $kernelParameters['kernel.project_dir'],
                );
            }
        }

        // For some reason Collection is abstract
        $bundleCollection = new class () extends Collection {};
        foreach (self::ADDITIONAL_BUNDLES as $bundle) {
            $bundle::register($bundleCollection);
        }

        return $bundleCollection->getElements();
    }

    public static function getDistPackages(): array
    {
        return include __DIR__ . '/../Packages.php';
    }

    public function build(ContainerBuilder $containerBuilder): void
    {
        parent::build($containerBuilder);

        $configLoader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__),
            $containerBuilder->getParameter('kernel.environment'),
        );
        $configLoader->load('Resources/config/packages/monolog.yaml');

        $loader = new XmlFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('Api/DependencyInjection/service.xml');
        $loader->load('Installation/DependencyInjection/service.xml');
        $loader->load('LocationFinder/DependencyInjection/controller.xml');
        $loader->load('PreferredDelivery/DependencyInjection/service.xml');

        $containerBuilder->addCompilerPass(new CarrierAdapterRegistryCompilerPass());
    }

    public function install(InstallContext $installContext): void
    {
        $this->loadDependenciesForSetup();

        $this->executeMigrationsOfBundles();

        BundleInstaller::createForContainerAndClass($this->container, self::class)
            ->install(self::ADDITIONAL_BUNDLES, $installContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        $this->loadDependenciesForSetup();

        $this->executeMigrationsOfBundles();

        BundleInstaller::createForContainerAndClass($this->container, self::class)
            ->install(self::ADDITIONAL_BUNDLES, $updateContext);
    }

    private function executeMigrationsOfBundles(): void
    {
        // All the services required for migration execution are private in the DI-Container. As a workaround the
        // services are instantiated explicitly here.
        $db = $this->container->get(Connection::class);
        // See vendor/symfony/monolog-bundle/Resources/config/monolog.xml on how the logger is defined.
        $logger = new Logger('app');
        $logger->useMicrosecondTimestamps($this->container->getParameter('monolog.use_microseconds'));
        $migrationCollectionLoader = new MigrationCollectionLoader(
            connection: $db,
            migrationRuntime: new MigrationRuntime($db, $logger),
            logger: $logger,
        );
        $migrationSource = new MigrationSource('PickwareDhl');

        foreach (self::ADDITIONAL_BUNDLES as $bundle) {
            $bundle::registerMigrations($migrationSource);
        }
        $migrationCollectionLoader->addSource($migrationSource);

        foreach ($migrationCollectionLoader->collectAll() as $migrationCollection) {
            $migrationCollection->sync();
            $migrationCollection->migrateInPlace();
        }
    }

    public function postInstall(InstallContext $installContext): void
    {
        PluginLifecycleErrorRecovery::createForContainer($this->container)
            ->recoverFromErrorsIn($this->handlePostInstall(...), $installContext);
    }

    private function handlePostInstall(InstallContext $installContext): void
    {
        PickwareDhlInstaller::initFromContainer($this->container)->postInstall($installContext);
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        PluginLifecycleErrorRecovery::createForContainer($this->container)
            ->recoverFromErrorsIn($this->handlePostUpdate(...), $updateContext);
    }

    private function handlePostUpdate(UpdateContext $updateContext): void
    {
        PickwareDhlInstaller::initFromContainer($this->container)->postUpdate($updateContext);

        if ($updateContext->getPlugin()->isActive()) {
            $this->copyAssetsFromBundles();

            BundleInstaller::createForContainerAndClass($this->container, self::class)
                ->onAfterActivate(self::ADDITIONAL_BUNDLES, $updateContext);
        }
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->loadDependenciesForSetup();

        DependencyAwareTableDropper::createForContainer($this->container)->dropTables([
            'pickware_dhl_sales_channel_api_context',
            // These are actually only tables from old plugin versions. We still remove them here just in case.
            'pickware_dhl_carrier',
            'pickware_dhl_document',
            'pickware_dhl_document_page_format',
            'pickware_dhl_document_shipment_mapping',
            'pickware_dhl_document_tracking_code_mapping',
            'pickware_dhl_document_type',
            'pickware_dhl_shipment',
            'pickware_dhl_shipment_order_delivery_mapping',
            'pickware_dhl_shipment_order_mapping',
            'pickware_dhl_shipping_method_config',
            'pickware_dhl_tracking_code',
        ]);

        $this->container->get(Connection::class)->executeStatement(
            'DELETE FROM system_config
            WHERE configuration_key LIKE :domain',
            ['domain' => DhlConfig::CONFIG_DOMAIN . '.%'],
        );

        PickwareDhlInstaller::initFromContainer($this->container)->uninstall($uninstallContext);
        BundleInstaller::createForContainerAndClass($this->container, self::class)->uninstall($uninstallContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        $this->copyAssetsFromBundles();

        BundleInstaller::createForContainerAndClass($this->container, self::class)
            ->onAfterActivate(self::ADDITIONAL_BUNDLES, $activateContext);
    }

    /**
     * Run the dependency loader for a setup step like install/update/uninstall
     *
     * When executing one of these steps but no Pickware plugin is activated, the dependency loader did never run until
     * the call of the corresponding method. You can trigger it with a call of this method.
     */
    private function loadDependenciesForSetup(): void
    {
        if (isset($GLOBALS['PICKWARE_DEPENDENCY_LOADER'])) {
            $plugins = $this->container->get('kernel')->getPluginLoader()->getPluginInfos();
            $projectDir = $this->container->getParameter('kernel.project_dir');
            $GLOBALS['PICKWARE_DEPENDENCY_LOADER']->ensureLatestDependenciesOfPluginsLoaded($plugins, $projectDir);
        }
    }

    private function copyAssetsFromBundles(): void
    {
        $this->container
            ->get('pickware_dhl.bundle_supporting_asset_service')
            ->copyAssetsFromBundle('PickwareShippingBundle');
    }
}
