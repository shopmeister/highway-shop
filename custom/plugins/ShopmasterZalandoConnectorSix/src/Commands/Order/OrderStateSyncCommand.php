<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Commands\Order;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use ShopmasterZalandoConnectorSix\Commands\ZalandoCommand;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusMessage;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\Status;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Messenger\MessageBusInterface;


#[AsCommand('sm:order:state-sync')]
class OrderStateSyncCommand extends ZalandoCommand
{
    public function __construct(
        private readonly EntityRepository    $repositoryOrder,
        private readonly MessageBusInterface $messageBus,
    )
    {
        parent::__construct();
    }

    const GTE_TIME = '-14 days';

    public function runProcess(): void
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter(
            'createdAt',
            [RangeFilter::GTE => date('Y-m-d', strtotime(self::GTE_TIME))]
        ))->addFilter(new RangeFilter(
            'createdAt',
            [RangeFilter::LT => date('Y-m-d H:i:s', strtotime('-1 hour'))]
        ))
            ->addFilter(
                new OrFilter([
                    new NotFilter(NotFilter::CONNECTION_AND, [
                        new EqualsAnyFilter('customFields.' . OrderCustomFields::CUSTOM_FIELD_STATUS_SENT, array_keys(Status::DELIVERY_STATUS))
                    ]),
                    new EqualsFilter('customFields.' . OrderCustomFields::CUSTOM_FIELD_STATUS_SENT, null)
                ])
            );

        $ids = $this->repositoryOrder->searchIds($criteria, $context)->getIds();
        foreach ($ids as $id) {
            $scope = new OrderScope($id);
            $message = new OrderDeliveryStatusMessage($scope, StateMachineTransitionEvent::class, $context);
            $this->messageBus->dispatch($message);
        }
    }
}