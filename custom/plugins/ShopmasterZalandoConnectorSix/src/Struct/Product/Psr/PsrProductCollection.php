<?php

namespace ShopmasterZalandoConnectorSix\Struct\Product\Psr;


use ShopmasterZalandoConnectorSix\Services\Config\ConfigServiceInterface;
use ShopmasterZalandoConnectorSix\Struct\Collection;

class PsrProductCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return PsrProductStruct::class;
    }

    /**
     * @return string[]
     */
    public function getEanList(): array
    {
        return array_map(function (PsrProductStruct $item) {
            return $item->getEan();
        }, $this->elements);
    }

    /**
     * @return array
     */
    public function getCountryCodes(): array
    {
        $data = [];
        /** @var PsrProductStruct $psrProduct */
        foreach ($this->elements as $psrProduct) {
            foreach ($psrProduct->getOffers() as $iso => $offer) {
                $data[] = strtolower($iso);
            }
        }
        return array_unique($data);
    }

    public function getSalesChannels(): array
    {
        $data = [];
        foreach ($this->getCountryCodes() as $countryCode) {
            $data[] = ConfigServiceInterface::SALES_CHANNELS[$countryCode];
        }
        return $data;
    }

    public function filterBySalesChannels(array $salesChannels): self
    {

    }
}