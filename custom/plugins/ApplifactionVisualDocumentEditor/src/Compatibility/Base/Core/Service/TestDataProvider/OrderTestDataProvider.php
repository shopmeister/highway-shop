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

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverInterface;
use DateTime;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeEntity;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDeliveryPosition\OrderDeliveryPositionCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDeliveryPosition\OrderDeliveryPositionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\TaxFreeConfig;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;

class OrderTestDataProvider
{

    const FAKE_ORDER_NUMBER_PREFIX = 'EXAMPLE-';
    const DOCUMENT_TYPE_INVOICE = 'invoice';

    private PreviewDataResolverInterface $previewDataResolver;

    public function __construct(
        PreviewDataResolverInterface $previewDataResolver
    ) {
        $this->previewDataResolver = $previewDataResolver;
    }

    public function getFakeOrder($documentType, Context $context, $itemCount = 20): OrderEntity
    {
        if (!$documentType) {
            $documentType = 'invoice';
        }

        $salesChannel = $this->previewDataResolver->getSalesChannel(null, $context);

        $order = new OrderEntity();
        $order->setSalesChannelId($salesChannel->getId());
        $order->setShippingCosts(new CalculatedPrice(4.99, 4.99, new CalculatedTaxCollection(), new TaxRuleCollection()));
        $order->setId(Uuid::randomHex());
        $currency = $this->createFakeCurrencyEntity();
        $order->setCurrency($currency);
        $order->setOrderNumber(self::FAKE_ORDER_NUMBER_PREFIX . '1234');
        $order->setTaxStatus(CartPrice::TAX_STATE_GROSS);
        $order->setLanguageId($context->getLanguageId());
        $language = new LanguageEntity();
        $locale = new LocaleEntity();
        $locale->setCode("de-DE");
        $language->setLocale($locale);
        $order->setLanguage($language);
        $order->setOrderDate(new DateTime('now'));
        $order->setOrderDateTime(new DateTime('now'));
        $order->setVersionId(Defaults::LIVE_VERSION);

        $lineItemCollection = new OrderLineItemCollection();
        $orderTaxTotals = [
            7 => 0,
            19 => 0
        ];

        for ($i = 1; $i <= $itemCount; $i++) {

            $lineItemPriceNet = $i * 10;
            $lineItem = new OrderLineItemEntity();
            $lineItem->setId(Uuid::randomHex());
            $lineItem->setIdentifier($lineItem->getId());
            $lineItem->setQuantity($i % 3 + 1);
            $lineItem->setGood(true);
            $lineItem->setLabel('Example Product ' . $i);
            $lineItem->setPosition($i);
            $lineItem->setPayload(['productNumber' => 'SKU-' . $i]);
            $taxRate = $i % 2 > 0 ? 19 : 7;
            $lineItemPriceGross = $lineItemPriceNet / 100 * (100 + $taxRate);
            $lineItem->setUnitPrice($lineItemPriceGross);
            $orderTaxTotals[$taxRate] += (($lineItemPriceGross - $lineItemPriceNet) * $lineItem->getQuantity());
            $lineItem->setPrice(new CalculatedPrice(
                $lineItemPriceGross,
                $lineItemPriceGross * $lineItem->getQuantity(),
                new CalculatedTaxCollection([
                    new CalculatedTax(
                        $lineItemPriceGross - $lineItemPriceNet,
                        $taxRate,
                        $lineItemPriceNet
                    ),
                ]),
                new TaxRuleCollection([
                    new TaxRule(
                        $taxRate,
                        100.0
                    ),
                ]),
            ));
            $lineItem->setPriceDefinition(new QuantityPriceDefinition($lineItemPriceNet, new TaxRuleCollection([
                new TaxRule(
                    $taxRate,
                    100.0
                ),
            ])));
            $order->setPositionPrice($lineItemPriceNet);
            if ($documentType != 'credit_note') {
                $lineItem->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
            } else {
                $lineItem->setType(LineItem::CREDIT_LINE_ITEM_TYPE);
            }
            $lineItem->setTotalPrice($lineItem->getUnitPrice() * $lineItem->getQuantity());
            $lineItemCollection->add($lineItem);

        }

        $order->setLineItems($lineItemCollection);

        $shippingCostTaxRate = 19;
        $shippingCostNet = 10;
        $shippingCostGross = $shippingCostNet / 100 * (100 + $shippingCostTaxRate);
        $shippingCostTax = $shippingCostGross - $shippingCostNet;
        $calculatedShippingCosts = new CalculatedPrice(
            $shippingCostGross,
            $shippingCostGross,
            new CalculatedTaxCollection([
                new CalculatedTax(
                    $shippingCostTax,
                    $shippingCostTaxRate,
                    $shippingCostNet
                ),
            ]),
            new TaxRuleCollection([
                new TaxRule(
                    $shippingCostTaxRate,
                    100.0
                ),
            ]),
        );
        $orderDeliveryPosition = new OrderDeliveryPositionEntity();
        $orderDeliveryPosition->setId(Uuid::randomHex());
        $orderDeliveryPosition->setUniqueIdentifier(Uuid::randomHex());
        $orderDeliveryPosition->setOrderLineItem($lineItemCollection->first());
        $orderDeliveryPosition->setQuantity($lineItemCollection->first()->getQuantity());
        $orderDeliveryPosition->setPrice($calculatedShippingCosts);

        $subtotalGross = $orderDeliveryPosition->getPrice()->getTotalPrice();
        $tax = $orderDeliveryPosition->getPrice()->getCalculatedTaxes()->getAmount();
        foreach ($lineItemCollection as $lineItem) {
            $subtotalGross += $lineItem->getPrice()->getTotalPrice();
            $tax += $lineItem->getPrice()->getCalculatedTaxes()->getAmount() * $lineItem->getQuantity();
        }
        $subtotalNet = $subtotalGross - $tax;

        $order->setPrice(new CartPrice(
            $subtotalNet,
            $subtotalGross,
            $subtotalGross,
            new CalculatedTaxCollection([
                new CalculatedTax(
                    $orderTaxTotals[19] + $shippingCostTax,
                    19.0,
                    $subtotalNet
                ),
                new CalculatedTax(
                    $orderTaxTotals[7],
                    7.0,
                    $subtotalNet
                ),
            ]),
            new TaxRuleCollection([
                new TaxRule(
                    19.0,
                    100.0
                ),
                new TaxRule(
                    7,
                    100.0
                ),
            ]),
            CartPrice::TAX_STATE_NET
        ));
        $order->setAmountNet($subtotalNet);
        $order->setAmountTotal($subtotalGross);

        $country = new CountryEntity();
        $country->setName('Germany');
        if (method_exists($country, 'setCompanyTax')) {
            $country->setCompanyTax(new TaxFreeConfig());
        } elseif(method_exists($country, 'setCompanyTaxFree')) {
            $country->setCompanyTaxFree(false);
        }
        $country->setIso('DE');
        $orderAddress = new OrderAddressEntity();
        $orderAddress->setCompany('German Company');
        $orderAddress->setFirstName('Max');
        $orderAddress->setLastName('Mustermann');
        $orderAddress->setStreet('Musterstraße 1');
        $orderAddress->setZipcode('12345');
        $orderAddress->setCity('Musterstadt');
        $orderAddress->setCountry($country);
        $orderAddress->setPhoneNumber('+49 (1234) 56789-1');
        $orderAddress->setId(Uuid::randomHex());
        $orderAddress->setCountry($country);
        $order->setBillingAddress($orderAddress);
        $order->setBillingAddressId($orderAddress->getId());
        $order->setAddresses(new OrderAddressCollection([$orderAddress]));
        $orderDelivery = new OrderDeliveryEntity();
        $orderDelivery->setId(Uuid::randomHex());
        $orderDelivery->setPositions(new OrderDeliveryPositionCollection([$orderDeliveryPosition]));
        $deliveryDate = new DateTime('now');
        $orderDelivery->setShippingDateEarliest($deliveryDate);
        $orderDelivery->setShippingDateLatest($deliveryDate);
        $shippingMethod = new ShippingMethodEntity();
        $shippingMethod->setName("Example Shipping");
        $shippingMethod->setTranslated(['name' => 'Example Shipping']);
        $orderDelivery->setShippingMethod($shippingMethod);
        $orderDelivery->setShippingOrderAddress($orderAddress);
        $orderDelivery->setShippingCosts($calculatedShippingCosts);
        $order->setDeliveries(new OrderDeliveryCollection([$orderDelivery]));
        $order->setShippingTotal($calculatedShippingCosts->getTotalPrice());
        $transaction = new OrderTransactionEntity();
        $transaction->setId(Uuid::randomHex());
        $paymentMethod = new PaymentMethodEntity();
        $paymentMethod->setName("Example Payment Method");
        $paymentMethod->setTranslated(['name' => 'Example Payment Method']);
        $transaction->setPaymentMethod($paymentMethod);
        $order->setTransactions(new OrderTransactionCollection([$transaction]));
        $orderCustomer = new OrderCustomerEntity();
        $orderCustomer->setId(Uuid::randomHex());
        $orderCustomer->setEmail('customer@example.org');
        $orderCustomer->setCustomerNumber('987654321');
        $orderCustomer->setCompany($orderAddress->getCompany());
        $orderCustomer->setFirstName($orderAddress->getFirstName());
        $orderCustomer->setLastName($orderAddress->getLastName());
        $orderCustomer->setVatIds(['UST-ID-12345']);
        $order->setOrderCustomer($orderCustomer);
        return $order;
    }

    public function getFakeDocument($documentType, OrderEntity $order): DocumentEntity
    {
        if ($documentType == null) {
            $documentType = self::DOCUMENT_TYPE_INVOICE;
        }

        $document = new DocumentEntity();
        $documentTypeEntity = new DocumentTypeEntity();
        $documentTypeEntity->setTechnicalName($documentType);
        $document->setDocumentType($documentTypeEntity);
        $document->setFileType('pdf');
        $document->setOrder($order);
        $currentDate = new DateTime('now');
        $config = [
            "name" => "invoice",
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
            "displayAdditionalNoteDelivery" => true
        ];
        if (str_starts_with($order->getOrderNumber(), self::FAKE_ORDER_NUMBER_PREFIX)) {
            $config = array_merge($config, [
                "isFakeOrder" => true
            ]);
        }
        switch ($documentType) {
            case "invoice": {
                    $customData = [
                        "custom" => [
                            "invoiceNumber" => "1234"
                        ]
                    ];
                    break;
                }
            case "credit_note": {
                    $customData = [
                        "custom" => [
                            "invoiceNumber" => "1234",
                            "creditNoteNumber" => "5678"
                        ]
                    ];
                    break;
                }
            case "delivery_note": {
                    $customData = [
                        "custom" => [
                            "deliveryNoteNumber" => "5678",
                            "deliveryDate" => $currentDate->format('c'),
                            "deliveryNoteDate" => $currentDate->format('c')
                        ]
                    ];
                    break;
                }
            case "storno": {
                    $customData = [
                        "custom" => [
                            "invoiceNumber" => "1234",
                            "stornoNumber" => "5678"
                        ]
                    ];
                    break;
                }
            default;
                $customData = [
                    "custom" => [
                        "invoiceNumber" => "1111",
                        "creditNoteNumber" => "2222",
                        "deliveryNoteNumber" => "3333",
                        "stornoNumber" => "4444",
                        "deliveryDate" => $currentDate->format('c'),
                        "deliveryNoteDate" => $currentDate->format('c')
                    ]
                ];
                break;
        }
        $config = array_merge($config, $customData);
        $document->setConfig($config);
        return $document;
    }

    private function createFakeCurrencyEntity(): CurrencyEntity
    {
        $currency = new CurrencyEntity();
        $currency->setIsoCode('EUR');
        return $currency;
    }
}
