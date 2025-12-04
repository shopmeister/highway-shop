<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button\Login\Struct;

use Shopware\Core\Framework\Struct\JsonSerializableTrait;

class AmazonLoginButtonPayloadStruct implements \JsonSerializable
{
    use JsonSerializableTrait;

    protected string $signInReturnUrl;

    protected string $storeId;

    protected array $signInScopes;

    public function getSignInReturnUrl(): string
    {
        return $this->signInReturnUrl;
    }

    public function setSignInReturnUrl(string $signInReturnUrl): void
    {
        $this->signInReturnUrl = $signInReturnUrl;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }

    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getSignInScopes(): array
    {
        return $this->signInScopes;
    }

    public function setSignInScopes(array $signInScopes): void
    {
        $this->signInScopes = $signInScopes;
    }
}
