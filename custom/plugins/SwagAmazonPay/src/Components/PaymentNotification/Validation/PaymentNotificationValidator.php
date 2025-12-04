<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PaymentNotification\Validation;

use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\PaymentNotification\Exception\InvalidPaymentNotificationException;
use Swag\AmazonPay\Components\PaymentNotification\Struct\PaymentNotificationMessage;
use Symfony\Component\HttpFoundation\Request;

class PaymentNotificationValidator implements PaymentNotificationValidatorInterface
{
    private ConfigServiceInterface $configService;

    public function __construct(ConfigServiceInterface $configService)
    {
        $this->configService = $configService;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Request $request, Context $context): PaymentNotificationMessage
    {
        $body = (string) $request->getContent(false);

        $notificationBody = \json_decode($body, true);

        if (empty($notificationBody) || !\array_key_exists('Message', $notificationBody)) {
            throw new InvalidPaymentNotificationException('Body can not be empty and has to be in JSON format.');
        }

        $message = new PaymentNotificationMessage();
        $message->assign(\json_decode($notificationBody['Message'], true));

        if ($message->getNotificationVersion() !== self::SUPPORTED_VERSION) {
            throw new InvalidPaymentNotificationException(\sprintf('The notification version [%s] is not supported. Use [%s] notifications instead.', $message->getNotificationVersion(), self::SUPPORTED_VERSION), $body);
        }

        // At this point, we validate if a config for the given merchant id exists, no exact configuration is needed at this point
        $configEntity = $this->configService->getConfigEntityByMerchantId($message->getMerchantId(), $context);
        if ($configEntity === null) {
            throw new InvalidPaymentNotificationException('Merchant-Id does not match the shop configuration.', $body);
        }

        return $message;
    }
}
