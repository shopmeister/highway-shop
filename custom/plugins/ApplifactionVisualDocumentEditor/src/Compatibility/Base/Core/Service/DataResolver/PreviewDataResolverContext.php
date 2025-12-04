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

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class PreviewDataResolverContext implements PreviewDataResolverContextInterface
{
    /**
     * @var Context $context
     */
    private $context;

    /**
     * @var SalesChannelEntity $salesChannel
     */
    private $salesChannel;

    public function __construct(Context $context, SalesChannelEntity $salesChannel)
    {
        $this->context = $context;
        $this->salesChannel = $salesChannel;
    }

    public function getContext(): Context {
        return $this->context;
    }

    public function getSalesChannel(): SalesChannelEntity {
        return $this->salesChannel;
    }
}
