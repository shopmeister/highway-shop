<?php

declare(strict_types=1);

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction;

use DateTimeInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class AmazonPayTransactionEntity extends Entity
{
    use EntityIdTrait;

    public const TRANSACTION_TYPE_CHARGE = 'Charge';
    public const TRANSACTION_TYPE_REFUND = 'Refund';
    public const TRANSACTION_TYPE_CHARGE_PERMISSION = 'ChargePermission';

    protected ?string $parentId = null;
    protected ?string $orderId = null;
    protected ?string $orderTransactionId = null;
    protected ?string $merchantId = null;
    protected ?string $reference = null;
    protected ?string $mode = null;
    protected ?string $type = null;
    protected ?DateTimeInterface $time = null;
    protected ?DateTimeInterface $expiration = null;
    protected ?float $amount = null;
    protected ?float $capturedAmount = null;
    protected ?float $refundedAmount = null;
    protected ?string $currency = null;
    protected ?string $status = null;
    protected ?bool $customerInformed = null;
    protected ?bool $adminInformed = null;

    protected ?OrderTransactionEntity $orderTransaction = null;
    protected ?OrderEntity $order = null;
    protected ?AmazonPayTransactionEntity $parent = null;

//    public function getUpdateData(){
//        return [
//            'id'=>$this->getId(),
//            'orderTransactionId'=>$this->getOrderTransactionId(),
//            'orderId'=>$this->getOrderId(),
//            'merchantId'=>$this->getMerchantId(),
//            'reference'=>$this->getReference(),
//            'mode'=>$this->getMode(),
//            'type'=>$this->getType(),
//            'time'=>$this->getTime(),
//            'expiration'=>$this->getExpiration(),
//            'amount'=>$this->getAmount(),
//            'capturedAmount'=>$this->getCapturedAmount(),
//            'refundedAmount'=>$this->getRefundedAmount(),
//            'currency'=>$this->getCurrency(),
//            'status'=>$this->getStatus(),
//            'customerInformed'=>$this->getCustomerInformed(),
//            'adminInformed'=>$this->getAdminInformed(),
//
//        ];
//    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): AmazonPayTransactionEntity
    {
        $this->parentId = $parentId;
        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): AmazonPayTransactionEntity
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getOrderTransactionId(): ?string
    {
        return $this->orderTransactionId;
    }

    public function setOrderTransactionId(?string $orderTransactionId): AmazonPayTransactionEntity
    {
        $this->orderTransactionId = $orderTransactionId;
        return $this;
    }

    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }

    public function setMerchantId(?string $merchantId): AmazonPayTransactionEntity
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): AmazonPayTransactionEntity
    {
        $this->reference = $reference;
        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): AmazonPayTransactionEntity
    {
        $this->mode = $mode;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): AmazonPayTransactionEntity
    {
        $this->type = $type;
        return $this;
    }

    public function getTime(): ?DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(?DateTimeInterface $time): AmazonPayTransactionEntity
    {
        $this->time = $time;
        return $this;
    }

    public function getExpiration(): ?DateTimeInterface
    {
        return $this->expiration;
    }

    public function setExpiration(?DateTimeInterface $expiration): AmazonPayTransactionEntity
    {
        $this->expiration = $expiration;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): AmazonPayTransactionEntity
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCapturedAmount(): ?float
    {
        return $this->capturedAmount;
    }

    public function setCapturedAmount(?float $capturedAmount): AmazonPayTransactionEntity
    {
        $this->capturedAmount = $capturedAmount;
        return $this;
    }

    public function getRefundedAmount(): ?float
    {
        return $this->refundedAmount;
    }

    public function setRefundedAmount(?float $refundedAmount): AmazonPayTransactionEntity
    {
        $this->refundedAmount = $refundedAmount;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): AmazonPayTransactionEntity
    {
        $this->currency = $currency;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): AmazonPayTransactionEntity
    {
        $this->status = $status;
        return $this;
    }

    public function getCustomerInformed(): ?bool
    {
        return $this->customerInformed;
    }

    public function setCustomerInformed(?bool $customerInformed): AmazonPayTransactionEntity
    {
        $this->customerInformed = $customerInformed;
        return $this;
    }

    public function getAdminInformed(): ?bool
    {
        return $this->adminInformed;
    }

    public function setAdminInformed(?bool $adminInformed): AmazonPayTransactionEntity
    {
        $this->adminInformed = $adminInformed;
        return $this;
    }

    public function getOrderTransaction(): ?OrderTransactionEntity
    {
        return $this->orderTransaction;
    }

    public function setOrderTransaction(?OrderTransactionEntity $orderTransaction): AmazonPayTransactionEntity
    {
        $this->orderTransaction = $orderTransaction;
        return $this;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): AmazonPayTransactionEntity
    {
        $this->order = $order;
        return $this;
    }

    public function getParent(): ?AmazonPayTransactionEntity
    {
        return $this->parent;
    }

    public function setParent(?AmazonPayTransactionEntity $parent): AmazonPayTransactionEntity
    {
        $this->parent = $parent;
        return $this;
    }

    public function serializeForUpdate(): array
    {
        $data = $this->jsonSerialize();
        unset($data['createdAt']);
        unset($data['extensions']);
        unset($data['_uniqueIdentifier']);
        unset($data['translated']);
        unset($data['updatedAt']);

        if (!empty($data['orderTransaction']) && $data['orderTransaction'] instanceof OrderTransactionEntity) {
            $data['orderTransactionId'] = $data['orderTransaction']->getId();
        }
        unset($data['orderTransaction']);

        if (!empty($data['order']) && $data['order'] instanceof OrderEntity) {
            $data['orderId'] = $data['order']->getId();
        }
        unset($data['order']);

        if (!empty($data['parent']) && $data['parent'] instanceof AmazonPayTransactionEntity) {
            $data['parentId'] = $data['parent']->getId();
        }
        unset($data['parent']);
        return $data;
    }

    public function toPublicArray(): array
    {
        $data = $this->jsonSerialize();
        unset($data['orderTransaction']);
        unset($data['order']);
        unset($data['parent']);
        return $data;
    }


}
