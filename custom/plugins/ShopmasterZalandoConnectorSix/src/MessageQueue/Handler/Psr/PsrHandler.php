<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Psr;

use ShopmasterZalandoConnectorSix\Exception\MessageQueue\ExceptionMessageQueue;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Price\ExportPriceByPsrMessage;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Price\ImportPriceReportByPsrMessage;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Stock\ExportStockByPsrMessage;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductStruct;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: MessagePsrInterface::class, priority: 5000)]
//#[AsMessageHandler(handles: ExportPriceByPsrMessage::class, priority: 5000)]
//#[AsMessageHandler(handles: ExportStockByPsrMessage::class, priority: 5000)]
class PsrHandler
{
    public function __construct(
        readonly private EntityRepository $repositoryProduct
    )
    {
    }

    /**
     * @param MessagePsrInterface $message
     * @return void
     */
    public function __invoke(MessagePsrInterface $message): void
    {
        $psr = $message->getPsr();
        $clone = clone $psr;

        if ($message->productIsRequired()) {
            $psr->clear();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('ean', $clone->getEanList()))
            ->addAssociation('prices');
        $searchResult = $this->repositoryProduct->search($criteria, Context::createDefaultContext());

        /** @var ProductEntity $entity */
        foreach ($searchResult as $entity) {
            /** @var PsrProductStruct $struct */
            $struct = $clone->get($entity->getEan())->setProduct($entity);
            $psr->set($entity->getEan(), $struct);
        }
    }

}