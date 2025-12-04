<?php
namespace NetzpShopmanager6\Components;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Symfony\Component\HttpFoundation\IpUtils;

class ShopmanagerVisitors
{
    final public const CURRENT_USERS_MINUTES_INTERVAL = 3;

    public function __construct(private readonly EntityRepository $statisticsRepository)
    {
    }

    public function logStats($context, $salesChannelId, $request)
    {
        $this->countVisitor($context, $salesChannelId, $request->getClientIp());
    }

    public function getCurrentUsers($context, $salesChannelId)
    {
        $start = (new \DateTime(self::CURRENT_USERS_MINUTES_INTERVAL . ' minutes ago'))->format('Y-m-d H:i:s');

        $criteria = new Criteria();
        $criteria->setLimit(1);
        if ($salesChannelId != '') {
            $criteria->addFilter(
                new EqualsFilter('salesChannelId', $salesChannelId)
            );
        }
        $criteria->addAggregation(
            new FilterAggregation('currentUsers',
                new CountAggregation('currentUsers', 'id'),
                [
                    new RangeFilter('updatedAt', [RangeFilter::GTE => $start])
                ]
            )
        );
        $users = $this->statisticsRepository->search($criteria, $context);

        return $users->getAggregations()->get('currentUsers')->getCount();
    }

    public function countVisitor($context, $salesChannelId, $clientIp)
    {
        $hash = $this->anonymize($clientIp);
        $nowStart = (new \DateTime())->format('Y-m-d 00:00:00');
        $nowEnd = (new \DateTime())->format('Y-m-d 23:59:59');

        $criteria = new Criteria();
        $criteria->setLimit(1);
        if($salesChannelId != '') {
            $criteria->addFilter(
                new EqualsFilter('salesChannelId', $salesChannelId)
            );
        }

        $criteria->addFilter(
            new EqualsFilter('hash', $hash),
            new RangeFilter('createdAt', [RangeFilter::GTE => $nowStart, RangeFilter::LTE => $nowEnd])
        );

        $item = $this->statisticsRepository->search($criteria, $context)->first();

        if($item) {
            $this->statisticsRepository->update([
                [
                    'id'             => $item->getId(),
                    'impressions'    => $item->getImpressions() + 1
                ],
            ], $context);
        }
        else {
            $this->statisticsRepository->create([
                [
                    'salesChannelId' => $salesChannelId,
                    'hash'           => $hash,
                    'impressions'    => 1
                ],
            ], $context);
        }
    }

    private function anonymize($clientIp)
    {
        return hash('sha256', IpUtils::anonymize($clientIp));
    }
}
