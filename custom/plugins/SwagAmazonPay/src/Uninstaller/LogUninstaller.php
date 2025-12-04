<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Uninstaller;

use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\Finder\Finder;

class LogUninstaller implements UninstallerInterface
{
    private string $logDirectory;

    public function __construct(string $logDirectory)
    {
        $this->logDirectory = $logDirectory;
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($this->logDirectory)->name('swag_amazon_pay_*.log');

        foreach ($finder as $file) {
            $absolutePath = $file->getRealPath();
            if ($absolutePath === false) {
                continue;
            }

            \unlink($absolutePath);
        }
    }
}
