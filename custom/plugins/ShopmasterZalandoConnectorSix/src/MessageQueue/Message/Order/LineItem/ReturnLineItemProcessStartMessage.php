<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\LineItem;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessageInterface;
use Shopware\Core\Framework\Context;

class ReturnLineItemProcessStartMessage implements MessageInterface
{
    public function __construct(
        protected string  $orderId,
        protected Context $context
    )
    {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return static
     */
    public function setOrderId(string $orderId): static
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @param Context $context
     * @return static
     */
    public function setContext(Context $context): static
    {
        $this->context = $context;
        return $this;
    }
}