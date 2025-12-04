<?php declare(strict_types=1);

namespace NetzpShopmanager6\Components;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Uninstaller
{
    public function __construct(private readonly ContainerInterface $container) {
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->removeAccessKey();
        $this->removeCustomfields();
        $this->removeMigrations();
    }

    public function removeAccessKey()
    {
        $defaultContext = new Context(new SystemSource());
        $repo = $this->container->get('integration.repository');
        $repo->delete([['id' => strtolower(Installer::INTEGRATION_ID)]],  $defaultContext);
    }

    public function removeCustomFields()
    {
        $defaultContext = new Context(new SystemSource());
        $customFieldsRepository = $this->container->get('custom_field_set.repository');
        $customFieldsRepository->delete([['id' => strtolower(Installer::CUSTOM_FIELDSET_ID)]], $defaultContext);
    }

    public function removeMigrations()
    {
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('DROP TABLE IF EXISTS `s_plugin_netzp_statistics`');
    }
}
