<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\PaymentNotification\Struct;

use Shopware\Core\Framework\Struct\Struct;

/*
 * Notice: Uppercase first letter of the properties to be compatible to the
 * Amazon Pay data structure.
 */
class PaymentNotificationMessage extends Struct
{
    protected string $MerchantID;

    protected string $ObjectId;

    protected string $ObjectType;

    protected ?string $ChargePermissionId = null;

    protected string $NotificationType;

    protected string $NotificationId;

    protected ?string $NotificationVersion = null;

    public function getMerchantId(): string
    {
        return $this->MerchantID;
    }

    public function getObjectId(): string
    {
        return $this->ObjectId;
    }

    public function getNotificationType(): string
    {
        return $this->NotificationType;
    }

    public function getChargePermissionId(): ?string
    {
        return $this->ChargePermissionId;
    }

    public function getNotificationId(): string
    {
        return $this->NotificationId;
    }

    public function getNotificationVersion(): ?string
    {
        return $this->NotificationVersion;
    }

    public function getObjectType(): string
    {
        return $this->ObjectType;
    }
}
