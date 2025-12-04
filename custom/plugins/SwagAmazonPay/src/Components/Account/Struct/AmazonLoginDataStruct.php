<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Account\Struct;

use Shopware\Core\Framework\Struct\Struct;

class AmazonLoginDataStruct extends Struct
{
    protected string $amazonAccountId;

    protected ?string $amazonAccountEmail = null;

    public function __construct(
        string $amazonAccountId,
        ?string $amazonAccountEmail = null
    ) {
        $this->amazonAccountId = $amazonAccountId;
        $this->amazonAccountEmail = $amazonAccountEmail;
    }

    public function getAmazonAccountId(): string
    {
        return $this->amazonAccountId;
    }

    public function getAmazonAccountEmail(): ?string
    {
        return $this->amazonAccountEmail;
    }
}
