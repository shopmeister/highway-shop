<?php

namespace ShopmasterZalandoConnectorSix\Struct;

abstract class Collection extends \Shopware\Core\Framework\Struct\Collection
{
    /**
     * @return array|null
     */
    public function toArray(): ?array
    {
        return json_decode(json_encode($this), true);
    }

    /**
     * @param self $collection
     * @return $this
     */
    public function merge(self $collection): self
    {
        $this->elements = array_merge($this->elements, $collection->getElements());
        return $this;
    }
}