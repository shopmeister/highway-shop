<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1737541193ChangeConfigFromParticipationToBillingNumbers extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1737541193;
    }

    public function update(Connection $connection): void
    {
        // Remove old Kleinpaket return participation config
        $connection->executeStatement(
            <<<SQL
                DELETE FROM `system_config`
                WHERE `configuration_key` = 'PickwareDhl.dhl.returnParticipationV62KP'
                SQL,
        );

        // Rename old DHL Paket return participation config from PickwareDhl.dhl.returnParticipationV01PAK to PickwareDhl.dhl.billingNumberDhlRetoure
        $connection->executeStatement(
            <<<SQL
                UPDATE `system_config`
                SET `configuration_key` = 'PickwareDhl.dhl.billingNumberdhlRetoure'
                WHERE `configuration_key` = 'PickwareDhl.dhl.returnParticipationV01PAK'
                SQL,
        );

        // Rename old DHL Paket participation configs from PickwareDhl.dhl.participationXX to PickwareDhl.dhl.billingNumberXX
        $connection->executeStatement(
            <<<SQL
                UPDATE `system_config`
                SET `configuration_key` = REPLACE(`configuration_key`, 'PickwareDhl.dhl.participation', 'PickwareDhl.dhl.billingNumber')
                WHERE `configuration_key` LIKE 'PickwareDhl.dhl.participation%'
                SQL,
        );

        // Migrate value of configs from participation to billing number format the new format is customerNumber + productCode + old config value
        $connection->executeStatement(
            <<<SQL
                UPDATE `system_config` AS `system_config_values`
                LEFT JOIN `system_config` AS `system_config_customer_number`
                    ON `system_config_values`.`sales_channel_id` <=> `system_config_customer_number`.`sales_channel_id`
                    AND `system_config_customer_number`.`configuration_key` = 'PickwareDhl.dhl.customerNumber'
                SET `system_config_values`.`configuration_value` = JSON_OBJECT(
                    '_value',
                    CONCAT(
                        JSON_UNQUOTE(JSON_EXTRACT(`system_config_customer_number`.`configuration_value`,'$._value')),
                        SUBSTRING(`system_config_values`.`configuration_key`, LOCATE('V', `system_config_values`.`configuration_key`) + 1, 2),
                        JSON_UNQUOTE(JSON_EXTRACT(`system_config_values`.`configuration_value`,'$._value'))
                    )
                )
                WHERE `system_config_values`.`configuration_key` LIKE 'PickwareDhl.dhl.billingNumberV%';
                SQL,
        );

        $connection->executeStatement(
            <<<SQL
                UPDATE `system_config` AS `system_config_values`
                LEFT JOIN `system_config` AS `system_config_customer_number`
                    ON `system_config_values`.`sales_channel_id` <=> `system_config_customer_number`.`sales_channel_id`
                    AND `system_config_customer_number`.`configuration_key` = 'PickwareDhl.dhl.customerNumber'
                SET `system_config_values`.`configuration_value` = JSON_OBJECT(
                    '_value',
                    CONCAT(
                        JSON_UNQUOTE(JSON_EXTRACT(`system_config_customer_number`.`configuration_value`,'$._value')),
                        '07',
                        JSON_UNQUOTE(JSON_EXTRACT(`system_config_values`.`configuration_value`,'$._value'))
                    )
                )
                WHERE `system_config_values`.`configuration_key` = 'PickwareDhl.dhl.billingNumberdhlRetoure';
                SQL
        );
    }

    public function updateDestructive(Connection $connection): void {}
}
