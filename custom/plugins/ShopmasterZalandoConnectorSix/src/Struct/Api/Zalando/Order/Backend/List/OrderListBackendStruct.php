<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend\List;

use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend\OrderBackendStruct;

class OrderListBackendStruct extends OrderBackendStruct
{
    public function jsonMapSerialize(): array
    {
        return [
            'zalando' => [
                'id' => $this->getId(),
                'orderNumber' => $this->getOrderNumber(),
                'status' => $this->getStatus(),
                'createdAt' => $this->getCreatedAt()
            ],
            'shopware' => [
                'orderNumber' => ($this->getOrderEntity()?->getOrderNumber())
            ]
        ];
    }
}