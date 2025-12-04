<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Core\Content\Flow\Dispatching\Action;

use Pickware\ShippingBundle\Shipment\ShipmentBlueprintCreationConfiguration;
use Pickware\ShippingBundle\Shipment\ShipmentService;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Event\OrderAware;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CreateReturnLabelAction extends FlowAction
{
    /**
     * @param ShipmentService $shipmentService
     * @param EntityRepository $shipmentRepository
     * @param EntityRepository $orderRepository
     * @param EntityRepository $customFieldSetRepository
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(
        private readonly ShipmentService $shipmentService,
        private readonly EntityRepository $shipmentRepository,
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $customFieldSetRepository,
        private readonly SystemConfigService $systemConfigService
    )
    {

    }

    public static function getName(): string
    {
        return 'action.create.return-label';
    }

    public function requirements(): array
    {
        return [OrderAware::class];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        $config = $flow->getConfig();

        if (isset($config['isActive'])) {
            if ($config['isActive']) {
                $order                                           = $flow->getData('order');
                $orderId                                         = $order->getId();
                $shipmentBlueprintCreationConfigurationParameter = [
                    'skipParcelRepacking' => true
                ];
                $shipmentBlueprintCreationConfiguration          = ShipmentBlueprintCreationConfiguration::fromArray(
                    $shipmentBlueprintCreationConfigurationParameter ?? [],
                );

                $returnShipmentBlueprint = $this->shipmentService->createReturnShipmentBlueprintForOrder(
                    $orderId,
                    $shipmentBlueprintCreationConfiguration,
                    $flow->getContext(),
                );

                if (isset($config['shippingCarrierMethod'][0]['id'])) {
                    $carrierName = $config['shippingCarrierMethod'][0]['id'];
                    $returnShipmentBlueprint->setCarrierTechnicalName($carrierName);
                } else {
                    $returnShipmentBlueprint->setCarrierTechnicalName('dhl');
                }

                $result = $this->shipmentService->createReturnShipmentForOrder($returnShipmentBlueprint, $orderId, $flow->getContext())->jsonSerialize();

                //save in custom field
                if (isset($result['successfullyOrPartlySuccessfullyProcessedShipmentIds'][0])) {
                    $shipmentId = $result['successfullyOrPartlySuccessfullyProcessedShipmentIds'][0];

                    $criteria = new Criteria([$shipmentId]);
                    $criteria->addAssociation('trackingCodes');
                    $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
                    if ($shipment = $this->shipmentRepository->search($criteria, $flow->getContext())->first()) {
                        $trackingCodes = $shipment->getTrackingCodes();
                        $trackingCode  = $trackingCodes->first();

                        if ($customFieldId = $this->systemConfigService->get('ShmCustomTasks.config.trackingCodeCustomField')) {
                            if ($customField = $this->customFieldSetRepository->search(new Criteria([$customFieldId]), Context::createDefaultContext())->first()) {
                                $data = [
                                    'id'           => $order->getId(),
                                    'customFields' => [
                                        $customField->getName() => $trackingCode->getTrackingCode()
                                    ]
                                ];
                                $this->orderRepository->update([$data], Context::createDefaultContext());
                            }
                        }

                    }
                }
            }
        }
    }
}