<?php
namespace Shm\ShmCustomTasks\Service\ScheduledTask;

use Pickware\ShippingBundle\Shipment\Model\ShipmentCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: PickwareTrackingExportTask::class)]
class PickwareTrackingExportTaskHandler extends ScheduledTaskHandler
{
    private EntityRepository $orderRepository;
    private EntityRepository $customFieldSetRepository;
    private SystemConfigService $systemConfigService;


    /**
         * @param EntityRepository $scheduledTaskRepository
         * @param EntityRepository $orderRepository
         * @param EntityRepository $customFieldSetRepository
         * @param SystemConfigService $systemConfigService

         */

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        EntityRepository $orderRepository,
        EntityRepository $customFieldSetRepository,
        SystemConfigService $systemConfigService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->orderRepository = $orderRepository;
        $this->customFieldSetRepository = $customFieldSetRepository;
        $this->systemConfigService = $systemConfigService;
        //TODO: $this->exceptionLogger = $exceptionLogger;
    }

    public static function getHandledMessages(): iterable
    {
        return [PickwareTrackingExportTask::class];
    }

    public function run(): void
    {
        echo "PickwareTrackingExportTaskHandler started.\n";
        $this->logger->info('PickwareTrackingExportTaskHandler started.');

        try {
            // $prevDate = (new \DateTime())->modify('-10 days');
            $prevDate = new \DateTime('now -10 day');
            echo "Previous date calculated: " . $prevDate->format(Defaults::STORAGE_DATE_TIME_FORMAT) . "\n";
            $this->logger->debug('Previous date calculated: ' . $prevDate->format(Defaults::STORAGE_DATE_TIME_FORMAT));

            $criteria = new Criteria();
            $criteria->addFilter(new RangeFilter('createdAt', [
                RangeFilter::GTE => $prevDate->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]));
            $criteria->addAssociation('pickwareShippingShipments');
            $criteria->addAssociation('pickwareShippingShipments.trackingCodes');

            $context = Context::createDefaultContext();
            $orders = $this->orderRepository->search($criteria, $context);

            echo "Number of orders found: " . $orders->count() . "\n";
            $this->logger->debug('Number of orders found: ' . $orders->count());
            $this->logger->debug('Orders data: ' . json_encode($orders->getElements()));

            if ($orders->count() > 0) {
                foreach ($orders as $order) {
                    $shipmentCollection = $order->get('pickwareShippingShipments');
                    echo "Shipment collection retrieved for order ID: " . $order->getId() . "\n";
                    $this->logger->debug('Shipment collection retrieved for order ID: ' . $order->getId());

                    $trackingNumber = $this->getTrackingNumbersFromPickware($shipmentCollection);

                    if ($trackingNumber) {
                        echo sprintf('Order ID %s has tracking number: %s', $order->getId(), $trackingNumber) . "\n";
                        $this->logger->info(sprintf('Order ID %s has tracking number: %s', $order->getId(), $trackingNumber));
                        $this->updateOrderTrackingNumber($order, $trackingNumber, $context);
                        $this->updateReturnTrackingNumber($order, $context);
                    } else {
                        echo sprintf('Order ID %s has no tracking number.', $order->getId()) . "\n";
                        $this->logger->warning(sprintf('Order ID %s has no tracking number.', $order->getId()));
                    }
                }
            } else {
                echo "No orders found for the given criteria.\n";
                $this->logger->info('No orders found for the given criteria.');
            }
        } catch (\Exception $e) {
            echo 'Error in PickwareTrackingExportTaskHandler: ' . $e->getMessage() . "\n";
            $this->logger->error('Error in PickwareTrackingExportTaskHandler: ' . $e->getMessage());
        }
    }

    private function updateOrderTrackingNumber($order, $trackingNumber, $context): void
    {
        try {
            echo 'Updating order tracking number for order ID: ' . $order->getId() . "\n";
            $this->logger->debug('Updating order tracking number for order ID: ' . $order->getId());

            $data = [
                'id' => $order->getId(),
                'customFields' => [
                    'sm_returnTrackingNumber' => $trackingNumber
                ]
            ];

            // Log the data that will be updated
            echo 'Data to update: ' . json_encode($data) . "\n";
            $this->logger->debug('Data to update: ' . json_encode($data));

            $this->orderRepository->update([$data], $context);

            // Log success
            echo 'Successfully updated tracking number for order ID: ' . $order->getId() . "\n";
            $this->logger->info('Successfully updated tracking number for order ID: ' . $order->getId());
        } catch (\Exception $e) {
            // Log any exceptions
            echo 'Error updating tracking number for order ID: ' . $order->getId() . '. Error: ' . $e->getMessage() . "\n";
            $this->logger->error('Error updating tracking number for order ID: ' . $order->getId() . '. Error: ' . $e->getMessage());
        }
    }

    private function updateReturnTrackingNumber($order, $context): void
    {
        try {
            echo 'Updating return tracking number for order ID: ' . $order->getId() . "\n";
            $this->logger->debug('Updating return tracking number for order ID: ' . $order->getId());

            $orderCustomFields = $order->getCustomFields();
            if ($orderCustomFields) {
                $sevenSendersCustomFieldId = $this->systemConfigService->get('ShmCustomTasks.config.sevenSendersCustomField');
                if ($sevenSendersCustomFieldId) {
                    $sevenSendersCustomField = $this->customFieldSetRepository->search(new Criteria([$sevenSendersCustomFieldId]), $context)->first();
                    if ($sevenSendersCustomField) {
                        $existingTrackingCodes = $orderCustomFields[$sevenSendersCustomField->getName()] ?? [];
                        if (!empty($existingTrackingCodes)) {
                            $returnTrackingNumber = end($existingTrackingCodes);
                            $data = [
                                'id' => $order->getId(),
                                'customFields' => [
                                    $sevenSendersCustomField->getName() => $returnTrackingNumber
                                ]
                            ];
                            $this->orderRepository->update([$data], $context);
                            echo 'Successfully updated return tracking number for order ID: ' . $order->getId() . "\n";
                            $this->logger->info('Successfully updated return tracking number for order ID: ' . $order->getId());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            echo 'Error updating return tracking number for order ID: ' . $order->getId() . '. Error: ' . $e->getMessage() . "\n";
            $this->logger->error('Error updating return tracking number for order ID: ' . $order->getId() . '. Error: ' . $e->getMessage());
        }
    }

    private function getTrackingNumbersFromPickware(ShipmentCollection $shipmentCollection): ?string
    {
        try {
            echo 'Retrieving tracking numbers from shipment collection.' . "\n";
            $this->logger->debug('Retrieving tracking numbers from shipment collection.');

            $mostRecentTrackingCode = null;

            foreach ($shipmentCollection as $shipment) {
                $trackingCodes = $shipment->getTrackingCodes();

                foreach ($trackingCodes as $code) {
                    if (!$mostRecentTrackingCode || $code->getCreatedAt() > $mostRecentTrackingCode->getCreatedAt()) {
                        $mostRecentTrackingCode = $code;
                    }
                }
            }

            if ($mostRecentTrackingCode) {
                echo sprintf('Most recent tracking code found: %s', $mostRecentTrackingCode->getTrackingCode()) . "\n";
                $this->logger->info(sprintf('Most recent tracking code found: %s', $mostRecentTrackingCode->getTrackingCode()));
            } else {
                echo 'No tracking codes found in the shipment collection.' . "\n";
                $this->logger->warning('No tracking codes found in the shipment collection.');
            }

            return $mostRecentTrackingCode ? $mostRecentTrackingCode->getTrackingCode() : null;
        } catch (\Exception $e) {
            echo 'Error retrieving tracking numbers from shipment collection. Error: ' . $e->getMessage() . "\n";
            $this->logger->error('Error retrieving tracking numbers from shipment collection. Error: ' . $e->getMessage());
            return null;
        }
    }
}
