<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Uninstaller;

use Shopware\Core\Framework\Plugin\Context\UninstallContext;

interface UninstallerInterface
{
    public function uninstall(UninstallContext $uninstallContext): void;
}
