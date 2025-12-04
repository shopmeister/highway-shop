<?php

namespace ShopmasterZalandoConnectorSix\Struct\Product\Psr;

use ShopmasterZalandoConnectorSix\Struct\Struct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferCollection;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferStruct;
use Shopware\Core\Content\Product\ProductEntity;

class PsrProductStruct extends Struct
{
    /**
     * @var string
     */
    protected string $ean;
    /**
     * @var PsrProductOfferCollection
     */
    protected PsrProductOfferCollection $offers;
    /**
     * @var ProductEntity|null
     */
    private ?ProductEntity $product = null;

    public function __construct()
    {
        $this->offers = new PsrProductOfferCollection();
    }

    /**
     * @return string
     */
    public function getEan(): string
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     * @return $this
     */
    public function setEan(string $ean): self
    {
        $this->ean = $ean;
        return $this;
    }

    /**
     * @return PsrProductOfferCollection
     */
    public function getOffers(): PsrProductOfferCollection
    {
        return $this->offers;
    }

    /**
     * @param PsrProductOfferStruct $offerStruct
     * @return $this
     */
    public function addOffer(PsrProductOfferStruct $offerStruct): self
    {
        $this->offers->set($offerStruct->getCountryCode(), $offerStruct);
        return $this;
    }

    /**
     * @return ProductEntity|null
     */
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity|null $product
     * @return self
     */
    public function setProduct(?ProductEntity $product): self
    {
        $this->product = $product;
        return $this;
    }
}