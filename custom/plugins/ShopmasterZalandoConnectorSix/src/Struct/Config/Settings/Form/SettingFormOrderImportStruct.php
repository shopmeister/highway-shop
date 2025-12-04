<?php

namespace ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\CanceledStatus;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\ReturnedStatus;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\ShippedStatus;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\Status;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\SettingsFormStruct;

class SettingFormOrderImportStruct extends SettingsFormStruct
{
    /**
     * @var bool
     */
    protected bool $active = false;
    /**
     * @var string
     */
    protected string $salesChannelId = '';
    /**
     * @var string
     */
    protected string $customerGroupId = '';
    /**
     * @var string
     */
    protected string $taxId = '';
    /**
     * @var string
     */
    protected string $orderStateId = '';
    /**
     * @var string
     */
    protected string $shippingMethodId = '';
    /**
     * @var string
     */
    protected string $paymentMethodId = '';
    /**
     * @var string
     */
    protected string $paymentStateId = '';
    /**
     * @var string
     */
    protected string $deliveryStateId = '';
    /**
     * @var array[]
     */
    protected array $updateDeliveryState = [
        ShippedStatus::CONFIG_NAME_STATE_IDS => [],
        CanceledStatus::CONFIG_NAME_STATE_IDS => [],
        ReturnedStatus::CONFIG_NAME_STATE_IDS => [],
    ];

    protected string $logisticCenterId = '';
    protected ?string $returnTrackingCustomField = null;

    /**
     * @return string|null
     */
    public function getPaymentStateId(): ?string
    {
        return $this->paymentStateId ?: null;
    }

    /**
     * @param string $paymentStateId
     * @return self
     */
    public function setPaymentStateId(string $paymentStateId): self
    {
        $this->paymentStateId = $paymentStateId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliveryStateId(): ?string
    {
        return $this->deliveryStateId ?: null;
    }

    /**
     * @param string $deliveryStateId
     * @return self
     */
    public function setDeliveryStateId(string $deliveryStateId): self
    {
        $this->deliveryStateId = $deliveryStateId;
        return $this;
    }


    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     */
    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * @return string
     */
    public function getCustomerGroupId(): string
    {
        return $this->customerGroupId;
    }

    /**
     * @param string $customerGroupId
     */
    public function setCustomerGroupId(string $customerGroupId): void
    {
        $this->customerGroupId = $customerGroupId;
    }

    /**
     * @return string
     */
    public function getTaxId(): string
    {
        return $this->taxId;
    }

    /**
     * @param string $taxId
     */
    public function setTaxId(string $taxId): void
    {
        $this->taxId = $taxId;
    }

    /**
     * @return string
     */
    public function getShippingMethodId(): ?string
    {
        return $this->shippingMethodId ?: null;
    }

    /**
     * @param string $shippingMethodId
     */
    public function setShippingMethodId(string $shippingMethodId): void
    {
        $this->shippingMethodId = $shippingMethodId;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId ?: null;
    }

    /**
     * @param string $paymentMethodId
     */
    public function setPaymentMethodId(string $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return self
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrderStateId(): ?string
    {
        return $this->orderStateId ?: null;
    }

    /**
     * @param string $orderStateId
     * @return self
     */
    public function setOrderStateId(string $orderStateId): self
    {
        $this->orderStateId = $orderStateId;
        return $this;
    }

    /**
     * @return array[]
     */
    public function getUpdateDeliveryState(): array
    {
        return $this->updateDeliveryState;
    }

    /**
     * @param array $updateDeliveryState
     * @return self
     */
    public function setUpdateDeliveryState(array $updateDeliveryState): self
    {
        $this->updateDeliveryState = $updateDeliveryState;
        return $this;
    }

    public function getUpdateDeliveryStatus(string $stateId): ?Status
    {
        foreach ($this->getUpdateDeliveryState() as $name => $ids) {
            if (in_array($stateId, $ids)) {
                $class = Status::CONFIG_NAME[$name];
                return new $class;
            }
        }
        return null;
    }

    public function getLogisticCenterId(): string
    {
        return $this->logisticCenterId;
    }

    /**
     * @param string $logisticCenterId
     * @return self
     */
    public function setLogisticCenterId(string $logisticCenterId): self
    {
        $this->logisticCenterId = $logisticCenterId;
        return $this;
    }

    public function getReturnTrackingCustomField(): ?string
    {
        return $this->returnTrackingCustomField;
    }

    /**
     * @param string|null $returnTrackingCustomField
     * @return self
     */
    public function setReturnTrackingCustomField(?string $returnTrackingCustomField): self
    {
        $this->returnTrackingCustomField = $returnTrackingCustomField;
        return $this;
    }
}