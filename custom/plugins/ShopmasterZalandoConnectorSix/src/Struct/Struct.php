<?php

namespace ShopmasterZalandoConnectorSix\Struct;

use ShopmasterZalandoConnectorSix\Struct\Interfaces\CustomFieldsInterface;

class Struct extends \Shopware\Core\Framework\Struct\Struct implements \Stringable
{
    /**
     * @param array $array
     * @return void
     */
    protected function convertDateTimePropertiesToJsonStringRepresentation(array &$array): void
    {
        parent::convertDateTimePropertiesToJsonStringRepresentation($array);
        foreach ($array as $key => $value) {
            if ($value === null) {
                unset($array[$key]);
            }
        }
        unset($array['extensions']);
    }

    /**
     * @return array|null
     */
    public function toArray(): ?array
    {
        return json_decode(json_encode($this), true);
    }

    public static function uuidToId(string $uuid)
    {
        return str_replace('-', '', $uuid);
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        if ($this instanceof CustomFieldsInterface) {
            $this->assign(['customFields' => $this->customFieldsData()]);
        }

        return parent::jsonSerialize();
    }
}