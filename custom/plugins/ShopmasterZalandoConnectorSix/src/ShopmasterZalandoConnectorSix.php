<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\CustomFieldManager;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * @zalandoDoc https://developers.merchants.zalando.com/docs/index.html
 */
class ShopmasterZalandoConnectorSix extends Plugin
{

    /**
     * @param ActivateContext $activateContext
     * @return void
     */
    public function activate(ActivateContext $activateContext): void
    {
        (new CustomFieldManager($this->container))->makeCustomFieldSets();
    }

    /**
     * @param UpdateContext $updateContext
     * @return void
     */
    public function update(UpdateContext $updateContext): void
    {
        (new CustomFieldManager($this->container))->makeCustomFieldSets();
    }

    /**
     * @param DeactivateContext $deactivateContext
     * @return void
     */
    public function deactivate(DeactivateContext $deactivateContext): void
    {
        (new CustomFieldManager($this->container))->deleteCustomFieldSets();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }
        try {
            $this->removeTables();
        } catch (\Throwable $exception) {
        }

    }

    /**
     * @throws Exception
     */
    private function removeTables(): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $sql = "DROP TABLE IF EXISTS `zalando_price_report`;";
        $connection->executeStatement($sql);
    }
}