<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\TestDataProvider;

use DateTime;
use Pickware\PickwareErpStarter\Address\Model\AddressEntity;
use Pickware\PickwareErpStarter\Supplier\Model\SupplierEntity;
use Pickware\PickwareErpStarter\SupplierOrder\Model\SupplierOrderEntity;
use Pickware\PickwareErpStarter\SupplierOrder\Model\SupplierOrderLineItemCollection;
use Pickware\PickwareErpStarter\SupplierOrder\Model\SupplierOrderLineItemEntity;
use Pickware\PickwareErpStarter\SupplierOrder\SupplierOrderDocumentType;
use Pickware\PickwareErpStarter\Warehouse\Model\WarehouseEntity;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\Tax\TaxEntity;

class SupplierOrderDataProvider
{

    public function getTestData()
    {
        $currentDate = new DateTime('now');

        $supplierAddress = new AddressEntity();
        $supplierAddress->setId(Uuid::randomHex());
        $supplierAddress->setCompany("Ho-Ho-Ho Company");
        $supplierAddress->setDepartment("Gift Department");
        $supplierAddress->setTitle("Dr. ");
        $supplierAddress->setFirstName("Santa");
        $supplierAddress->setLastName("Claus");
        $supplierAddress->setStreet("Eleven Street");
        $supplierAddress->setHouseNumber("1");
        $supplierAddress->setAddressAddition("Miracle district");
        $supplierAddress->setZipCode("54321");
        $supplierAddress->setCity("Northpoletown");
        $supplierAddress->setEmail("santa@northpole.example");
        $supplierAddress->setPhone("01234 / 567890");

        $supplier = new SupplierEntity();
        $supplier->setId(Uuid::randomHex());
        $supplier->setNumber("11111");
        $supplier->setAddress($supplierAddress);

        $warehouseAddress = new AddressEntity();
        $warehouseAddress->setId(Uuid::randomHex());
        $warehouseAddress->setCompany("Example Company");
        $warehouseAddress->setStreet("Warehouse Street");
        $warehouseAddress->setHouseNumber("99b");
        $warehouseAddress->setAddressAddition("Warehouse A");
        $warehouseAddress->setZipCode("98765");
        $warehouseAddress->setCity("Warehousecity");

        $warehouse = new WarehouseEntity();
        $warehouse->setId(Uuid::randomHex());
        $warehouse->setAddress($warehouseAddress);

        $currency = new CurrencyEntity();
        $currency->setId(Uuid::randomHex());
        $currency->setUniqueIdentifier($currency->getId());
        $currency->setIsoCode('de_DE');
        $currency->setIsoCode('EUR');
        $currency->setFactor(1);
        $currency->setPosition(1);
        $currency->setSymbol('€');
        $currency->setItemRounding(new CashRoundingConfig(2, 0.01, true));
        $currency->setTotalRounding(new CashRoundingConfig(2, 0.01, true));

        $tax7 = new TaxEntity();
        $tax7->setId(Uuid::randomHex());
        $tax7->setUniqueIdentifier($currency->getId());
        $tax7->setTaxRate(7);
        $tax7->setName('Reduced Rate');

        $tax19 = new TaxEntity();
        $tax19->setId(Uuid::randomHex());
        $tax19->setUniqueIdentifier($currency->getId());
        $tax19->setTaxRate(19);
        $tax19->setName('Standard Rate');

        // Line item 1
        $lineItem1Manufacturer = new ProductManufacturerEntity();
        $lineItem1Manufacturer->setName("Ho-Ho-Ho Company");
        $lineItem1Product = new ProductEntity();
        $lineItem1Product->setId(Uuid::randomHex());
        $lineItem1Product->setProductNumber("RTC123-7643-12345-4432-7655");
        $lineItem1Product->setManufacturer($lineItem1Manufacturer);
        $lineItem1Product->setTax($tax19);
        $lineItem1Product->setPrice(new PriceCollection([new Price($currency->getId(), 10, 10 * ($tax19->getTaxRate() / 100 + 1), true)]));
        $lineItem1Product->setPurchasePrices(new PriceCollection([new Price($currency->getId(), 5, 5 * ($tax19->getTaxRate() / 100 + 1), true)]));
        $lineItem1Product->setExtensions([
            "pickwareErpProductSupplierConfiguration" => [
                "supplierProductNumber" => "RR187-1357-46853-22224"
            ]
        ]);
        $lineItem1 = new SupplierOrderLineItemEntity();
        $lineItem1->setUniqueIdentifier(Uuid::randomHex());
        $lineItem1->setProductSnapshot([
            "name" => "Red toy car",
            "productNumber" => "RTC123-7643-12345-4432-7655"
        ]);
        $lineItem1->setProduct($lineItem1Product);
        $lineItem1->setQuantity(1);

        // Line item 2
        $lineItem2Manufacturer = new ProductManufacturerEntity();
        $lineItem2Manufacturer->setName("Football Company");
        $lineItem2Product = new ProductEntity();
        $lineItem2Product->setId(Uuid::randomHex());
        $lineItem2Product->setProductNumber("DH1337");
        $lineItem2Product->setManufacturer($lineItem2Manufacturer);
        $lineItem2Product->setTax($tax7);
        $lineItem2Product->setPrice(new PriceCollection([new Price($currency->getId(), 10, 10 * ($tax7->getTaxRate() / 100 + 1), true)]));
        $lineItem2Product->setPurchasePrices(new PriceCollection([new Price($currency->getId(), 5, 5 * ($tax7->getTaxRate() / 100 + 1), true)]));
        $lineItem2Product->setExtensions([
            "pickwareErpProductSupplierConfiguration" => [
                "supplierProductNumber" => "7331HD"
            ]
        ]);
        $lineItem2 = new SupplierOrderLineItemEntity();
        $lineItem2->setUniqueIdentifier(Uuid::randomHex());
        $lineItem2->setProductSnapshot([
            "name" => "Football with snowflake pattern",
            "productNumber" => "DH1337"
        ]);
        $lineItem2->setProduct($lineItem2Product);
        $lineItem2->setQuantity(10);

        // Line item 3
        $lineItem3Manufacturer = new ProductManufacturerEntity();
        $lineItem3Manufacturer->setName("Ho-Ho-Ho Company");
        $lineItem3Product = new ProductEntity();
        $lineItem3Product->setId(Uuid::randomHex());
        $lineItem3Product->setProductNumber("TB133754468975323567");
        $lineItem3Product->setManufacturer($lineItem3Manufacturer);
        $lineItem3Product->setTax($tax19);
        $lineItem3Product->setPrice(new PriceCollection([new Price($currency->getId(), 10, 10 * ($tax19->getTaxRate() / 100 + 1), true)]));
        $lineItem3Product->setPurchasePrices(new PriceCollection([new Price($currency->getId(), 5, 5 * ($tax19->getTaxRate() / 100 + 1), true)]));
        $lineItem3Product->setExtensions([
            "pickwareErpProductSupplierConfiguration" => [
                "supplierProductNumber" => "TB135785442578975"
            ]
        ]);
        $lineItem3 = new SupplierOrderLineItemEntity();
        $lineItem3->setUniqueIdentifier(Uuid::randomHex());
        $lineItem3->setProductSnapshot([
            "name" => "Fluffy teddybear",
            "productNumber" => "TB133754468975323567"
        ]);
        $lineItem3->setProduct($lineItem3Product);
        $lineItem3->setQuantity(100);

        $lineItems = new SupplierOrderLineItemCollection();
        $lineItems->add($lineItem1);
        $lineItems->add($lineItem2);
        $lineItems->add($lineItem3);

        $supplierOrder = new SupplierOrderEntity();
        $supplierOrder->setUniqueIdentifier(Uuid::randomHex());
        $supplierOrder->setNumber("12345");
        $supplierOrder->setSupplier($supplier);
        $supplierOrder->setWarehouse($warehouse);
        $supplierOrder->setLineItems($lineItems);
        $supplierOrder->setCurrency($currency);

        return [
            "supplierOrder" => $supplierOrder,
            "pickingRouteNode" => [
                "stocks" => []
            ],
            "config" => [
                "name" => SupplierOrderDocumentType::TECHNICAL_NAME,
                "title" => null,
                "vatId" => "UST-ID-123456789",
                "global" => true,
                "bankBic" => "BIC-123456789",
                "bankIban" => "IBAN-123456789",
                "bankName" => "Bankname-12345",
                "pageSize" => "a4",
                "taxNumber" => "VAT-ID-123456789",
                "taxOffice" => "Example Tax Office",
                "companyUrl" => "https://example.com",
                "extensions" => [],
                "translated" => [],
                "companyName" => "Example Company",
                "companyEmail" => "info@example.com",
                "companyPhone" => "+49 1234 456 789",
                "documentDate" => $currentDate->format('c'),
                "itemsPerPage" => "6",
                "displayFooter" => true,
                "displayHeader" => true,
                "displayPrices" => true,
                "companyAddress" => "Example Company, Address 12, 3456 Exampletown",
                "documentNumber" => "1234",
                "documentTypeId" => "cf499ecd8616472295824f370561c45d",
                "documentType" => [
                    "technicalName" => SupplierOrderDocumentType::TECHNICAL_NAME
                ],
                "filenamePrefix" => "invoice_",
                "filenameSuffix" => "",
                "documentComment" => "",
                "pageOrientation" => "portrait",
                "displayLineItems" => true,
                "displayPageCount" => true,
                "deliveryCountries" => [
                    "0542063911324b1b91b274d1d2caba2a",
                    "0672ec8b561d4cd080a7f10d3a483042",
                    "080434f2e4c140bb869509ced5eeb23c",
                    "08ba7673a70e416aac918817d0441dff",
                    "16df3b1172b0429780cc969faa66eb90",
                    "17e924be16a44e95aebb415fbcf163a4",
                    "223df3d772774991ab27a09069b7fc6d",
                    "2aa279070df848409e8539613b3e0b3c",
                    "3494b0b7064b48e08e9d0dd3d4baf606",
                    "3d53caa4e02b4f15b0468302fb01a5e4",
                    "5881c37afdcb463d83a9105c671b390c",
                    "5d2831c79bce4fd897b5eaed01073f88",
                    "5f7bdcd36b584d7793f4c2957c349130",
                    "7d7308082c3f411d891ad15175f80f00",
                    "8262509d7adf46498bfacb2a31b94d97",
                    "893a01b08e71445199def36252a0d491",
                    "976fb82c1ec7417c98fe27ef4a794c3b",
                    "afd9ba03b699428584409b2f3d6864d3",
                    "bc01fc9b1ee646ad83e30085c2fd3915",
                    "bf23afded4c54cc98be6301154cb6798",
                    "c3ffb687ae874cea9223e3fdbad74725",
                    "c500ae283bbe4d1cb153b020a8f670fc",
                    "ce67b5ae3b8c49edbf89119a770ee90f",
                    "d0ffb6978da346aeb8faf35704255ab1",
                    "d4ae74f46834403b847680de1b4e25f9",
                    "dd94b5593a1540daadaa552a52d24b4a",
                    "ecd78cfa944a4bc781ecd43ed1dfe5d5",
                    "f59409e8fe5a444ea9bc6c55e1ae1285"
                ],
                "executiveDirector" => "John Doe",
                "placeOfFulfillment" => "PLACE-OF-FULFILLMENT-12345",
                "placeOfJurisdiction" => "PLACE-OF-JURISDICTION-12345",
                "displayCompanyAddress" => true,
                "diplayLineItemPosition" => true,
                "displayLineItemPosition" => false,
                "displayAdditionalNoteDelivery" => true,
                "isPreview" => true
            ]
        ];
    }
};
