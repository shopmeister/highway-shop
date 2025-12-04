<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\PaymentNotification;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PaymentNotificationEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $orderTransactionId = null;

    protected string $objectType;

    protected string $objectId;

    protected ?string $chargePermissionId = null;

    protected string $notificationId;

    protected string $notificationVersion;

    protected string $notificationType;

    protected string $expirationDate;

    protected string $reasonCode;

    protected string $reasonDescription;

    protected ?OrderTransactionEntity $orderTransaction = null;

    protected bool $processed;

    public function getOrderTransactionId(): ?string
    {
        return $this->orderTransactionId;
    }

    public function setOrderTransactionId(?string $orderTransactionId): void
    {
        $this->orderTransactionId = $orderTransactionId;
    }

    public function getObjectType(): string
    {
        return $this->objectType;
    }

    public function setObjectType(string $objectType): void
    {
        $this->objectType = $objectType;
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function getChargePermissionId(): ?string
    {
        return $this->chargePermissionId;
    }

    public function setChargePermissionId(?string $chargePermissionId): void
    {
        $this->chargePermissionId = $chargePermissionId;
    }

    public function getNotificationId(): string
    {
        return $this->notificationId;
    }

    public function setNotificationId(string $notificationId): void
    {
        $this->notificationId = $notificationId;
    }

    public function getNotificationVersion(): string
    {
        return $this->notificationVersion;
    }

    public function setNotificationVersion(string $notificationVersion): void
    {
        $this->notificationVersion = $notificationVersion;
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function setNotificationType(string $notificationType): void
    {
        $this->notificationType = $notificationType;
    }

    public function getExpirationDate(): string
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(string $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }

    public function setReasonCode(string $reasonCode): void
    {
        $this->reasonCode = $reasonCode;
    }

    public function getReasonDescription(): string
    {
        return $this->reasonDescription;
    }

    public function setReasonDescription(string $reasonDescription): void
    {
        $this->reasonDescription = $reasonDescription;
    }

    public function getOrderTransaction(): ?OrderTransactionEntity
    {
        return $this->orderTransaction;
    }

    public function setOrderTransaction(?OrderTransactionEntity $orderTransaction): void
    {
        $this->orderTransaction = $orderTransaction;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }
}
