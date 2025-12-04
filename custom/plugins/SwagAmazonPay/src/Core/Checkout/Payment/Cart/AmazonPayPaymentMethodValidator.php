<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Core\Checkout\Payment\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Checkout\Payment\Cart\Error\PaymentMethodBlockedError;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\Button\Validation\ExcludedProductValidator;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;

class AmazonPayPaymentMethodValidator implements CartValidatorInterface
{
    private ExcludedProductValidator $excludedProductValidator;

    public function __construct(ExcludedProductValidator $excludedProductValidator)
    {
        $this->excludedProductValidator = $excludedProductValidator;
    }

    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        if ($context->getPaymentMethod()->getId() !== PaymentMethodInstaller::AMAZON_PAYMENT_ID) {
            return;
        }

        if (!$this->excludedProductValidator->cartContainsExcludedProduct($cart, $context)) {
            return;
        }

        $errors->add(
            new PaymentMethodBlockedError((string) $context->getPaymentMethod()->getTranslation('name'))
        );
    }
}
