<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Storefront\Page\Extension;

use Shopware\Core\Framework\Struct\Struct;

class AmazonPayConfirmExtension extends Struct
{
    /**
     * The name of this extension. Use it to identify the correct extension.
     */
    public const EXTENSION_NAME = 'SwagAmazonPayConfirm';

    protected ?string $checkoutSessionId = null;

    protected bool $isOneClickCheckout;

    protected ?string $paymentDescriptor = null;

    public function isOneClickCheckout(): bool
    {
        return $this->isOneClickCheckout;
    }

    public function setIsOneClickCheckout(bool $isOneClickCheckout): self
    {
        $this->isOneClickCheckout = $isOneClickCheckout;

        return $this;
    }

    public function getCheckoutSessionId(): ?string
    {
        return $this->checkoutSessionId;
    }

    public function setCheckoutSessionId(?string $checkoutSessionId): AmazonPayConfirmExtension
    {
        $this->checkoutSessionId = $checkoutSessionId;

        return $this;
    }

    public function getPaymentDescriptor(): ?string
    {
        return $this->paymentDescriptor;
    }

    public function setPaymentDescriptor(?string $paymentDescriptor): AmazonPayConfirmExtension
    {
        $this->paymentDescriptor = $paymentDescriptor;

        return $this;
    }
}
