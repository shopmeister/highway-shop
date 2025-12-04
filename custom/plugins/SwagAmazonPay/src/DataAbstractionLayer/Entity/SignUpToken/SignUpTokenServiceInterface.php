<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\SignUpToken;

use Shopware\Core\Framework\Context;

interface SignUpTokenServiceInterface
{
    public function create(Context $context): string;

    public function validate(string $id, Context $context): bool;

    public function cleanup(Context $context): void;
}
