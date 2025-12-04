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

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\TypeResolverInterface;
use Shopware\Core\Framework\Context;

interface PreviewDataResolverInterface
{

    public function addResolver(TypeResolverInterface $resolver);

    public function getSalesChannel(?string $salesChannelId, Context $context);

    public function resolveType(Context $context, string $type, ?string $salesChannelId);

    public function getAdditionalTypeInformation(string $type, Context $context): array;

}
