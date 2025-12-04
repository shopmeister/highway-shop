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

class Migration1715772623DropPickwareSalesChannelContextSchema extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1715772623;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE `pickware_dhl_sales_channel_api_context`;');
    }

    public function updateDestructive(Connection $connection): void {}
}
