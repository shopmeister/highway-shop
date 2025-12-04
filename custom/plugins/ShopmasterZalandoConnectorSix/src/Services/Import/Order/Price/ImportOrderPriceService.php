<?php

namespace ShopmasterZalandoConnectorSix\Services\Import\Order\Price;

use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderStruct;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Tax\TaxEntity;

class ImportOrderPriceService
{
    private ConfigService $configService;
    private EntityRepository $repositoryTax;
    private CashRounding $cashRounding;

    public function __construct(
        ConfigService             $configService,
        EntityRepository $repositoryTax,
        CashRounding              $cashRounding
    )
    {
        $this->configService = $configService;
        $this->repositoryTax = $repositoryTax;
        $this->cashRounding = $cashRounding;
    }

    public function calculateOrderCartPrice(OrderStruct $order, Context $context): CartPrice
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        $taxRule = $this->getTaxRuleByTaxId($config->getTaxId(), $context);
        $totalPrice = $order->getOrderLinesPriceAmount();
        $calculatedTax = $this->getCalculatedTax($totalPrice, $taxRule, $context);
        $netPrice = $this->getNetPrice($totalPrice, $calculatedTax->getTax(), $context);
        return new CartPrice(
            $netPrice,
            $totalPrice,
            $netPrice,
            new CalculatedTaxCollection([$calculatedTax]),
            new TaxRuleCollection([$taxRule]),
            CartPrice::TAX_STATE_GROSS
        );
    }

    public function calculateOrderItemCartPrice(OrderLineStruct $item, string $salesChannelId, Context $context): CalculatedPrice
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($salesChannelId);
        $taxRule = $this->getTaxRuleByTaxId($config->getTaxId(), $context);
        $unitPrice = $item->getPrice();
        $totalPrice = $unitPrice;
        $calculatedTax = $this->getCalculatedTax($unitPrice, $taxRule, $context);
        return new CalculatedPrice(
            $unitPrice,
            $totalPrice,
            new CalculatedTaxCollection([$calculatedTax]),
            new TaxRuleCollection([$taxRule])
        );
    }

    private function getTaxRuleByTaxId(string $taxId, Context $context): TaxRule
    {
        $criteria = new Criteria([$taxId]);
        /** @var TaxEntity $tax */
        $tax = $this->repositoryTax->search($criteria, $context)->first();
        return new TaxRule($tax->getTaxRate());
    }

    private function getCalculatedTax(float $gross, TaxRule $taxRule, Context $context): CalculatedTax
    {
        $calculatedTax = $gross / ((100 + $taxRule->getTaxRate()) / 100) * ($taxRule->getTaxRate() / 100);
        $calculatedTax = $this->cashRounding->mathRound($calculatedTax, $context->getRounding());
        return new CalculatedTax($calculatedTax, $taxRule->getTaxRate(), $gross);
    }

    private function getNetPrice(float $totalPrice, float $tax, Context $context): float
    {
        return $this->cashRounding->mathRound(
            $totalPrice - $tax,
            $context->getRounding()
        );
    }

    public function calculateShippingCosts(OrderStruct $order, Context $context): CalculatedPrice
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        $taxRule = $this->getTaxRuleByTaxId($config->getTaxId(), $context);
        $totalPrice = 0.0;
        $calculatedTax = $this->getCalculatedTax($totalPrice, $taxRule, $context);
        $netPrice = $this->getNetPrice($totalPrice, $calculatedTax->getTax(), $context);
        return new CalculatedPrice(
            $netPrice,
            $totalPrice,
            new CalculatedTaxCollection([$calculatedTax]),
            new TaxRuleCollection([$taxRule]),
            1
        );
    }

    public function calculatePaymentTotal(OrderStruct $order, Context $context): CalculatedPrice
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        $taxRule = $this->getTaxRuleByTaxId($config->getTaxId(), $context);
        $totalPrice = $order->getOrderLinesPriceAmount();
        $calculatedTax = $this->getCalculatedTax($totalPrice, $taxRule, $context);
//        $netPrice = $this->getNetPrice($totalPrice, $calculatedTax->getTax(), $context);
        return new CalculatedPrice(
            $totalPrice,
            $totalPrice,
            new CalculatedTaxCollection([$calculatedTax]),
            new TaxRuleCollection([$taxRule])
        );
    }

}