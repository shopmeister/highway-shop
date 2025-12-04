<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Order\DeliveryStatus;

use Pickware\ShippingBundle\Shipment\Model\ShipmentCollection;
use Pickware\ShippingBundle\Shipment\Model\TrackingCodeEntity;
use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class TrackingNumbersService
{
    public function __construct(
        private readonly ConfigService $configService
    )
    {
    }

    public function getTrackingNumbers(OrderEntity $order, Context $context): array //todo  make via Struct or Collection
    {
        /** @var ShipmentCollection|null $shipmentCollection */
        if ($shipmentCollection = $order->getExtension('pickwareShippingShipments')) {
            list($trackingNumber, $returnTrackingNumber) = $this->getTrackingNumbersFromPickware($shipmentCollection);
            if (!empty($trackingNumber) && !empty($returnTrackingNumber)) {
                return [$trackingNumber, $returnTrackingNumber];
            }
        }
        $customFields = $order->getCustomFields() ?? [];
        /** @var OrderDeliveryEntity $delivery */
        $delivery = $order->getDeliveries()->first();
        $trackingCodes = $delivery->getTrackingCodes();
        $trackingNumber = trim(reset($trackingCodes) ?: '');
        $config = $this->configService->getImportOrderConfigBySalesChannelId($customFields[OrderCustomFields::CUSTOM_FIELD_SALES_CHANNEL_ID]);
        $returnTrackingCustomField = ($config->getReturnTrackingCustomField()) ?: OrderCustomFields::CUSTOM_FIELD_RETURN_TRACKING_NUMBER;
        $returnTrackingNumber = trim($customFields[$returnTrackingCustomField] ?? '');
        return [$trackingNumber, $returnTrackingNumber];
    }

    private function getTrackingNumbersFromPickware(ShipmentCollection $shipmentCollection): array
    {
        $trackingNumber = null;
        $returnTrackingNumber = null;
        foreach ($shipmentCollection as $shipment) {
            $trackingNumbers = $shipment->getTrackingCodes()->fmap(function (TrackingCodeEntity $code) {
                if (($code->getMetaInformation()['type'] ?? '') === 'shipmentNumber') {
                    return $code->getTrackingCode();
                }
            }) ?? [];
            $trackingNumbers = array_filter($trackingNumbers);
            if (!empty($trackingNumbers)) {
                $trackingNumber = reset($trackingNumbers);
            }


            $returnTrackingNumbers = $shipment->getTrackingCodes()->fmap(function (TrackingCodeEntity $code) {
                if (($code->getMetaInformation()['type'] ?? '') === 'returnShipmentNumber') {
                    return $code->getTrackingCode();
                }
            }) ?? [];
            $returnTrackingNumbers = array_filter($returnTrackingNumbers);
            if (!empty($returnTrackingNumbers)) {
                $returnTrackingNumber = reset($returnTrackingNumbers);
            }
        }
        return [$trackingNumber, $returnTrackingNumber];
    }
}