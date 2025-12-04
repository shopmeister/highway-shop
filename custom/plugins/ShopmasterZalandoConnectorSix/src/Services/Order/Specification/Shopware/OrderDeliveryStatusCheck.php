<?php

namespace ShopmasterZalandoConnectorSix\Services\Order\Specification\Shopware;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\Status;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderDeliveryStatusCheck implements ZalandoOrderCheckInterface
{
    private ConfigService $configService;

    public function __construct(
        ConfigService $configService
    )
    {
        $this->configService = $configService;
    }

    public function isAvailable(OrderEntity $order, Context $context): bool
    {
        $customFields = $order->getCustomFields() ?? [];
        if (empty($zmSalesChannelId = $customFields[OrderCustomFields::CUSTOM_FIELD_SALES_CHANNEL_ID])) {
            return false;
        }
        $deliveries = $order->getDeliveries();
        if (!$deliveries?->count()) {
            return false;
        }

        $orderImportConfig = $this->configService->getImportOrderConfigBySalesChannelId($zmSalesChannelId);
        if (empty($orderImportConfig->getSalesChannelId())) {
            return true;
        }
        /** @var OrderDeliveryEntity $orderDelivery */
        foreach ($deliveries as $orderDelivery) {
            //todo make validation include state history
            if ($status = $orderImportConfig->getUpdateDeliveryStatus($orderDelivery->getStateId())) {
                $context->assign([Status::ORDER_ZALANDO_STATUS => $status]);
                return true;
            }
        }
        return false;
    }


}