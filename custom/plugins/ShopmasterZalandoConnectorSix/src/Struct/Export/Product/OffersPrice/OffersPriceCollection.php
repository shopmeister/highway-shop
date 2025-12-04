<?php

namespace ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice;

use ShopmasterZalandoConnectorSix\Struct\Collection;

class OffersPriceCollection extends Collection
{

    /**
     * @param string $code
     * @param OffersPriceStruct $struct
     * @return void
     */
    public function addByCountryCode(string $code, OffersPriceStruct $struct)
    {
        $key = $this->getKey($code, $struct->getEan());
        $this->set($key, $struct);
    }

    /**
     * @param string $code
     * @param string $ean
     * @return OffersPriceStruct|null
     */
    public function getByCountyCodeAndEan(string $code, string $ean): ?OffersPriceStruct
    {
        $key = $this->getKey($code, $ean);
        return $this->elements[$key] ?? null;
    }

    /**
     * @param string $code
     * @param string $ean
     * @return string
     */
    private function getKey(string $code, string $ean): string
    {
        return md5(strtolower($code) . $ean);
    }

    public function getArticleNumberByEan(string $ean): ?string
    {
        /** @var OffersPriceStruct $struct */
        foreach ($this->elements as $struct) {
            if ($struct->getEan() === $ean) {
                return $struct->getArticleNumber();
            }
        }
        return null;
    }
}