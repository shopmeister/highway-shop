<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

final class SwkwebHideSoldoutProducts extends Plugin
{
    public function activate(ActivateContext $activateContext): void
    {
        $this->runIndexer();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->dropSchema();
    }

    private function dropSchema(): void
    {
        assert($this->container !== null);

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $connection->executeStatement('DROP TABLE IF EXISTS swkweb_hide_soldout_products_product_availability');
    }

    private function runIndexer(): void
    {
        $indexer = $this->container?->get(EntityIndexerRegistry::class);
        assert($indexer instanceof EntityIndexerRegistry);

        $indexer->sendIndexingMessage(['product.indexer']);
    }
}
