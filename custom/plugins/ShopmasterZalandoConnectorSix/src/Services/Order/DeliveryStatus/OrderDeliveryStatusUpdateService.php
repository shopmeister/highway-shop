<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Order\DeliveryStatus;

use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use ShopmasterZalandoConnectorSix\Exception\ZalandoException;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Order\ApiZalandoOrderService;
use ShopmasterZalandoConnectorSix\Services\Order\LineItem\LineItemService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderSetStruct;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class OrderDeliveryStatusUpdateService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface                         $logger,
        private readonly ApiZalandoOrderService $apiZalandoOrderService,
        private readonly EntityRepository       $repositoryOrder,
        private readonly TrackingNumbersService $trackingNumbersService,
        private readonly LineItemService        $lineItemService
    )
    {
        $this->logger = $logger->withName('OrderDeliveryStatusUpdateService');
    }

    public function updateInZalando(OrderScope $scope, Context $context): void
    {
        $order = $scope->getOrderEntity();
        try {
            $this->saveTrackingNumbers($order, $context);
            $this->lineItemService->updateLineItems($scope, $context);
            $customFields = $order->getCustomFields() ?? [];
            $customFields[OrderCustomFields::CUSTOM_FIELD_STATUS_SENT] = $scope->getStatus()->getStatus();
            $this->repositoryOrder->update([[
                'id' => $order->getId(),
                'customFields' => $customFields
            ]], $context);
        } catch (ZalandoException $e) {

        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }

    }

    /**
     * @throws ZalandoException
     */
    private function saveTrackingNumbers(OrderEntity $order, Context $context): void
    {
        $customFields = $order->getCustomFields();
        list($trackingNumber, $returnTrackingNumber) = $this->trackingNumbersService->getTrackingNumbers($order, $context);

        if (empty($trackingNumber) || empty($returnTrackingNumber)) {
            $this->logger->warning("Order track numbers empty [trackingNumber: {$trackingNumber} , returnTrackingNumber: {$returnTrackingNumber} ] Order ID:" . $customFields[OrderCustomFields::CUSTOM_FIELD_ORDER_ID]);
            throw new ZalandoException('TrackingNumber', 10200);
        }

        $orderSetStruct = new OrderSetStruct();
        $orderSetStruct->setId($customFields[OrderCustomFields::CUSTOM_FIELD_ORDER_ID])
            ->setTrackingNumber($trackingNumber)
            ->setReturnTrackingNumber($returnTrackingNumber);
        try {
            $response = $this->apiZalandoOrderService->saveOrder($orderSetStruct);
            $this->logger->info("Order track numbers save [trackingNumber: {$trackingNumber} , returnTrackingNumber: {$returnTrackingNumber} ] Response ID:" . $orderSetStruct->getId(), $response->getContentArray() ?? []);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        } finally {
            sleep(5); // Make sure to UPDATE tracking information i.e. tracking_number and return_tracking_number BEFORE you ship items. If you place calls to update tracking information and Order Line status close together (within 5 seconds), they may be processed out-of-sequence, which can cause difficulties. Hence recommended delay is 5 seconds.
        }

    }

}