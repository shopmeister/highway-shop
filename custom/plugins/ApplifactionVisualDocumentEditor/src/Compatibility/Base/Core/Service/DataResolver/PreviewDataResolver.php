<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\TypeResolverInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class PreviewDataResolver implements PreviewDataResolverInterface
{
    /**
     * @var EntityRepository
     */
    private EntityRepository $salesChannelRepository;

    /**
     * @var iterable|TypeResolverInterface[]
     */
    private $resolvers;

    public function __construct(
        EntityRepository $salesChannelRepository,
        iterable $resolvers
    ) {
        $this->salesChannelRepository = $salesChannelRepository;
        $this->resolvers = $resolvers;
    }

    public function addResolver(TypeResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
    }

    public function getSalesChannel(?string $salesChannelId, Context $context): SalesChannelEntity
    {
        $criteria = new Criteria();

        if ($salesChannelId) {
            $criteria->setIds([$salesChannelId]);
        }

        $criteria->addAssociation('domains');
        $criteria->addFilter(new EqualsFilter('active', 1));
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING));

        $resultsWithDomains = $this->salesChannelRepository->search($criteria, $context)->filter(function (SalesChannelEntity $channel) {
            return ($channel->getDomains() &&
                $channel->getDomains()->count() &&
                strncmp($channel->getDomains()->first()->getUrl(), 'http', 4) === 0
            );
        });

        $result = $resultsWithDomains->first();

        if (!$result) {
            throw new RuntimeException('Preview needs at least one active sales channel with a domain.', 1005);
        }

        return $result;
    }

    public function resolveType(Context $context, string $type, ?string $salesChannelId)
    {
        $salesChannel = $this->getSalesChannel($salesChannelId, $context);

        $resolverContext = new PreviewDataResolverContext($context, $salesChannel);

        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolveType($type)) {
                return $resolver->resolve($resolverContext, $type);
            }
        }

        return [];
    }

    public function getAdditionalTypeInformation(string $type, Context $context): array
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolveType($type)) {
                return [
                    'additionalTypes' => $resolver->getAdditionalDataTypes($type, $context),
                    'availableAssociations' => $resolver->getAssociations($type),
                ];
            }
        }

        return [];
    }
}
