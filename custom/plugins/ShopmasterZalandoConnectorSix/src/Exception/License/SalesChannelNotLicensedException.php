<?php

namespace ShopmasterZalandoConnectorSix\Exception\License;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class SalesChannelNotLicensedException extends ShopwareHttpException
{
    public function __construct(string $salesChannelId)
    {
        parent::__construct(
            'Sales channel "{{ salesChannelId }}" requires an additional license. Purchase at Shopware Store.',
            ['salesChannelId' => $salesChannelId]
        );
    }

    public function getErrorCode(): string
    {
        return 'SHOPMASTER_ZALANDO_CONNECTOR__SALES_CHANNEL_NOT_LICENSED';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
