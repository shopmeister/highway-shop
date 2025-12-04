<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

use Exception;
use Applifaction\DragNDropDocumentEditor\Compatibility\DependencyLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Shopware\Core\Kernel;
use Symfony\Component\DependencyInjection\Container;

require_once __DIR__ . '/../vendor/autoload.php';

class ApplifactionVisualDocumentEditor extends Plugin
{

    /**
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->container = $container;

        # load the dependencies that are compatible
        # with our current shopware version
        $loader = new DependencyLoader($this->container);
        $loader->loadServices();
    }

    /**
     * @param RoutingConfigurator $routes
     * @param string $environment
     * @return void
     */
    public function configureRoutes(RoutingConfigurator $routes, string $environment): void
    {
        if (!$this->isActive()) {
            return;
        }

        /** @var Container $container */
        $container = $this->container;

        $loader = new DependencyLoader($container);

        $routeDir = $loader->getRoutesPath($this->getPath());

        $fileSystem = new Filesystem();

        if ($fileSystem->exists($routeDir)) {
            $routes->import($routeDir . '/{routes}/*' . Kernel::CONFIG_EXTS, 'glob');
            $routes->import($routeDir . '/{routes}/' . $environment . '/**/*' . Kernel::CONFIG_EXTS, 'glob');
            $routes->import($routeDir . '/{routes}' . Kernel::CONFIG_EXTS, 'glob');
            $routes->import($routeDir . '/{routes}_' . $environment . Kernel::CONFIG_EXTS, 'glob');
        }
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->dropTables();
    }

    private function dropTables(): void
    {
        $connection = $this->container->get(Connection::class);
        $connection->executeUpdate('DROP TABLE IF EXISTS `dde_editor_state_translation`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `dde_editor_state`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `dde_custom_preset`');
    }
}
