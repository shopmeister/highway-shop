<?php
namespace NetzpShopmanager6\Components;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Shopware\Core\System\StateMachine\Transition;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use DateTime;
use DateInterval;
use Symfony\Component\Routing\RouterInterface;

class ShopmanagerHelper
{
    final public const API_VERSION       = 22; // 13.11.2021
    final public const PRICEMODE_GROSS   = 'gross';
    final public const PRICEMODE_NET     = 'net';
    final public const FILTER_NONE       = -999;

    final public const FEATURE_EDIT_ARTICLES = false; // can edit some article options (active, shipping free etc.?)

    public function __construct(private readonly Connection $connection,
                                private readonly SystemConfigService $config,
                                private readonly StateMachineRegistry $stateMachineRegistry,
                                private readonly RouterInterface $router,
                                private readonly EntityRepository $orderRepository,
                                private readonly EntityRepository $customerRepository,
                                private readonly EntityRepository $productRepository,
                                private readonly EntityRepository $salesChannelRepository,
                                private readonly EntityRepository $shippingMethodRepository,
                                private readonly EntityRepository $stateMmachineRepository,
                                private readonly EntityRepository $stateMachineStateRepository,
                                private readonly EntityRepository $paymentMethodRepository,
                                private readonly string $shopwareVersion,
                                private readonly string $shopwareRevision)
    {
    }

    public function getApiVersion()
    {
        return self::API_VERSION;
    }

    public function getSalesChannels(Context $context, $salesChannelId = '', array $restrictToSalesChannels = [])
    {
        $criteria = new Criteria();
        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('id', $salesChannelId));
        }
        elseif (count($restrictToSalesChannels) > 0) {
            $criteria->addFilter(new EqualsAnyFilter('id', $restrictToSalesChannels));
        }
        $criteria->addAssociation('currency');
        $criteria->addAssociation('domains');

        $shops = $this->salesChannelRepository->search($criteria, $context)->getEntities();

        $data = [];
        foreach($shops as $shop)
        {
            $config = $this->config->get('NetzpShopmanager6.config', $shop->getId());
            $domain = $shop->getDomains()->first();

            $data[] = [
                'currency'       => $shop->getCurrency()->getIsoCode(),
                'currencysymbol' => $shop->getCurrency()->getSymbol(),
                'currencypos'    => $shop->getCurrency()->getPosition(),
                'shopid'         => $shop->getId(),
                'version'        => $this->shopwareVersion,
                'revision'       => $this->shopwareRevision,
                'shopname'       => $shop->getTranslated()['name'] ?? $shop->getName(),
                'shoptitle'      => '', // not available in SW 6
                'shophost'       => $domain ? $domain->getUrl() : '',
                'apiversion'     => self::API_VERSION,
                'maintenance'    => $shop->isMaintenance(),
                'message'        => $config['message'] ?? ''
            ];
        }

        return $data;
    }

    public function getStates(Context $context, $group)
    {
        $data = [];
        if($group == 'dispatch')
        {
            $criteria = new Criteria();
            $criteria->addSorting(new FieldSorting('name'));
            $shippingMethods = $this->shippingMethodRepository->search($criteria, $context)->getEntities();

            foreach($shippingMethods as $shippingMethod) {
                $data[] = [
                    'id'          => $shippingMethod->getId(),
                    'name'        => $shippingMethod->getName(),
                    'description' => $shippingMethod->getDescription() ?: $shippingMethod->getName()
                ];
            }
        }
        elseif($group == 'state')
        {
            $criteria = new Criteria();
            $criteria->addAssociation('states');
            $criteria->addFilter(new EqualsFilter('technicalName', 'order.state'));
            $criteria->getAssociation('states')->addSorting(new FieldSorting('name'));

            $stateMachine = $this->stateMmachineRepository->search($criteria, $context)->getEntities()->first();
            if($stateMachine != null) {
                foreach($stateMachine->getStates() as $orderState)
                {
                    $data[] = [
                        'id'          => $orderState->getId(),
                        'name'        => $orderState->getTechnicalName(),
                        'description' => $orderState->getName()
                    ];
                }
            }
        }
        elseif($group == 'payment')
        {
            $criteria = new Criteria();
            $criteria->addAssociation('states');
            $criteria->addFilter(new EqualsFilter('technicalName', 'order_transaction.state'));
            $criteria->getAssociation('states')->addSorting(new FieldSorting('name'));
            $stateMachine = $this->stateMmachineRepository->search($criteria, $context)->getEntities()->first();

            if($stateMachine != null)
            {
                foreach($stateMachine->getStates() as $paymentState)
                {
                    $data[] = [
                        'id'          => $paymentState->getId(),
                        'name'        => $paymentState->getTechnicalName(),
                        'description' => $paymentState->getName()
                    ];
                }
            }
        }

        elseif($group == 'paymentmeans')
        {
            $criteria = new Criteria();
            $criteria->addSorting(new FieldSorting('name'));
            $paymentMethods = $this->paymentMethodRepository->search($criteria, $context)->getEntities();

            foreach($paymentMethods as $paymentMethod)
            {
                $data[] = [
                    'id'          => $paymentMethod->getId(),
                    'name'        => $paymentMethod->getName(),
                    'description' => $paymentMethod->getName()
                ];
            }
        }

        elseif($group == 'delivery')
        {
            $criteria = new Criteria();
            $criteria->addAssociation('states');
            $criteria->addFilter(new EqualsFilter('technicalName', 'order_delivery.state'));
            $criteria->getAssociation('states')->addSorting(new FieldSorting('name'));
            $stateMachine = $this->stateMmachineRepository->search($criteria, $context)->getEntities()->first();

            if($stateMachine != null)
            {
                foreach($stateMachine->getStates() as $paymentState)
                {
                    $data[] = [
                        'id'          => $paymentState->getId(),
                        'name'        => $paymentState->getTechnicalName(),
                        'description' => $paymentState->getName()
                    ];
                }
            }
        }

        return $data;
    }

    public function setOrderState(Context $context, $orderNumber, $state)
    {
        $data = [];

        $context->setConsiderInheritance(true);
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderNumber', $orderNumber));
        $order = $this->orderRepository->search($criteria, $context)->getEntities()->first();

        if($order)
        {
            $newStateName = $this->getInternalStateMachineState($context, $state);
            if($newStateName != '')
            {
                try {
                    $this->stateMachineRegistry->transition(
                        new Transition('order', $order->getId(), $newStateName, 'stateId'),
                        $context
                    );
                }
                catch (\Exception $ex) {
                    $data['error'] = $ex->getMessage();
                }
            }
        }

        return $data;
    }

    public function setPaymentState(Context $context, $orderNumber, $state)
    {
        $data = [];

        $context->setConsiderInheritance(true);

        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('orderNumber', $orderNumber));

        $criteria->addAssociation('transactions');
        $criteria->getAssociation('transactions')
            ->addSorting(new FieldSorting('updatedAt'))
            ->addSorting(new FieldSorting('createdAt'));

        $order = $this->orderRepository->search($criteria, $context)->getEntities()->first();

        if($order)
        {
            $transaction = $order->getTransactions()->last();
            if($transaction)
            {
                $newStateName = $this->getInternalStateMachineState($context, $state);
                if($newStateName != '')
                {
                    try {
                        $this->stateMachineRegistry->transition(
                            new Transition('order_transaction', $transaction->getId(), $newStateName, 'stateId'),
                            $context
                        );
                    }
                    catch (\Exception $ex) {
                        $data['error'] = $ex->getMessage();
                    }
                }
            }
        }

        return $data;
    }

    public function setDeliveryState(Context $context, $orderNumber, $state)
    {
        $data = [];

        $context->setConsiderInheritance(true);

        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('orderNumber', $orderNumber));
        $criteria->addAssociation('deliveries');
        $criteria->getAssociation('deliveries')
            ->addSorting(new FieldSorting('updatedAt'))
            ->addSorting(new FieldSorting('createdAt'));

        $order = $this->orderRepository->search($criteria, $context)->getEntities()->first();

        if($order)
        {
            $delivery = $order->getDeliveries()->last();
            if($delivery)
            {
                $newStateName = $this->getInternalStateMachineState($context, $state);
                if($newStateName != '')
                {
                    try {
                        $this->stateMachineRegistry->transition(
                            new Transition('order_delivery', $delivery->getId(), $newStateName, 'stateId'),
                            $context
                        );
                    }
                    catch (\Exception $ex) {
                        $data['error'] = $ex->getMessage();
                    }
                }
            }
        }

        return $data;
    }

    private function getInternalStateMachineState(Context $context, $stateId)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $stateId));
        $state = $this->stateMachineStateRepository->search($criteria, $context)->getEntities()->first();

        if($state == null) {
            return '';
        }

        $technicalName = $state->getTechnicalName();
        $stateName = '';

        $stateName = match ($technicalName)
        {
            'cancelled'          => StateMachineTransitionActions::ACTION_CANCEL,
            'completed'          => StateMachineTransitionActions::ACTION_COMPLETE,
            'open'               => StateMachineTransitionActions::ACTION_REOPEN,
            'in_progress'        => StateMachineTransitionActions::ACTION_PROCESS,
            'reminded'           => StateMachineTransitionActions::ACTION_REMIND,
            'failed'             => StateMachineTransitionActions::ACTION_FAIL,
            'refunded'           => StateMachineTransitionActions::ACTION_REFUND,
            'paid'               => StateMachineTransitionActions::ACTION_PAID,
            'chargeback'         => StateMachineTransitionActions::ACTION_CHARGEBACK,
            'authorized'         => StateMachineTransitionActions::ACTION_AUTHORIZE,
            'refunded_partially' => StateMachineTransitionActions::ACTION_REFUND_PARTIALLY,
            'paid_partially'     => StateMachineTransitionActions::ACTION_PAID_PARTIALLY,
            'returned_partially' => StateMachineTransitionActions::ACTION_RETOUR_PARTIALLY,
            'returned'           => StateMachineTransitionActions::ACTION_RETOUR,
            'shipped_partially'  => StateMachineTransitionActions::ACTION_SHIP_PARTIALLY,
            'shipped'            => StateMachineTransitionActions::ACTION_SHIP,

            default              => $stateName,
        };

        return $stateName;
    }

    public function getOrders(Context $context, $salesChannelId = '', $listLimit = 50, $search = '', $filter = [])
    {
        $data = [];

        $context->setConsiderInheritance(true);
        $criteria = new Criteria();
        $criteria->addAssociation('orderCustomer.salutation');
        $criteria->addAssociation('currency');
        $criteria->addAssociation('stateMachineState');
        $criteria->addAssociation('transactions.paymentMethod');
        $criteria->addAssociation('transactions.stateMachineState');
        $criteria->addAssociation('deliveries.shippingMethod');
        $criteria->addAssociation('deliveries.stateMachineState');

        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }

        if($search != '') {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new ContainsFilter('orderNumber', $search),
                new ContainsFilter('orderCustomer.firstName', $search),
                new ContainsFilter('orderCustomer.lastName', $search),
            ]));
        }

        if($filter != null && is_array($filter) && count($filter) > 0)
        {
            if(array_key_exists('dispatch', $filter) && (int)$filter['dispatch'] != self::FILTER_NONE) {
                $criteria->addFilter(new EqualsFilter('deliveries.shippingMethod.id',
                    $filter['dispatch']));
            }
            if(array_key_exists('orderstatus', $filter) && (int)$filter['orderstatus'] != self::FILTER_NONE) {
                $criteria->addFilter(new EqualsFilter('stateId',
                    $filter['orderstatus']));
            }
            if(array_key_exists('paymentmean', $filter) && (int)$filter['paymentmean'] != self::FILTER_NONE) {
                $criteria->addFilter(new EqualsFilter('transactions.paymentMethod.id',
                    $filter['paymentmean']));
            }
            if(array_key_exists('paymentstatus', $filter) && (int)$filter['paymentstatus'] != self::FILTER_NONE) {
                $criteria->addFilter(new EqualsFilter('transactions.stateId',
                    $filter['paymentstatus']));
            }
            if(array_key_exists('deliverystatus', $filter) && (int)$filter['deliverystatus'] != self::FILTER_NONE) {
                $criteria->addFilter(new EqualsFilter('deliveries.stateId',
                    $filter['deliverystatus']));
            }
        }

        $criteria->addSorting(new FieldSorting('orderDateTime', FieldSorting::DESCENDING));

        $criteria->getAssociation('transactions')
            ->addSorting(new FieldSorting('updatedAt'))
            ->addSorting(new FieldSorting('createdAt'));

        $criteria->getAssociation('deliveries')
            ->addSorting(new FieldSorting('updatedAt'))
            ->addSorting(new FieldSorting('createdAt'));

        $criteria->setLimit($listLimit);

        $orders = $this->orderRepository->search($criteria, $context)->getEntities();
        $n = 1;
        foreach($orders as $order)
        {
            $transaction = $order->getTransactions()->last();
            $delivery = $order->getDeliveries()->last();
            $customer = $order->getOrderCustomer();

            $data[] = [
                'id'             => $order->getId(),
                'number'         => $order->getOrderNumber(),
                'status'         => 0,
                'cleared'        => 0,
                'invoiceAmount'  => $order->getAmountTotal(),
                'orderTime'      => $order->getOrderDateTime()->format('c'),
                'currency'       => $order->getCurrency()->getIsoCode(),

                'customer'       => [
                    'id'            => $customer->getCustomerId(),
                    'number'        => $customer->getCustomerNumber(),
                    'firstname'     => $customer->getFirstname(),
                    'lastname'      => $customer->getLastname(),
                    'email'         => $customer->getEmail(),
                    'title'         => $customer->getSalutation()?->getSalutationKey() ?? '',
                    'birthday'      => '',
                    'internalcomment'=>'',
                ],

                'payment'        => [
                    'name'          => $transaction?->getPaymentMethod()?->getName() ?? '',
                    'description'   => $transaction?->getPaymentMethod()?->getDescription() ?? ''
                ],
                'paymentStatus'  => [
                    'name'          => $transaction?->getStateMachineState()?->getTechnicalName() ?? '',
                    'description'   => $transaction?->getStateMachineState()?->getName() ?? ''
                ],

                'dispatch'       => [
                    'name'          => $delivery?->getShippingMethod()?->getName() ?? '',
                    'description'   => $delivery?->getShippingMethod()?->getDescription() ?? ''
                ],
                'orderStatus'    => [
                    'name'          => $order->getStateMachineState()->getTechnicalName(),
                    'description'   => $order->getStateMachineState()->getName()
                ],
                'deliveryStatus' => [
                    'name'          => $delivery?->getStateMachineState()->getTechnicalName() ?? '',
                    'description'   => $delivery?->getStateMachineState()->getName() ?? ''
                ],
                'pos'            => $n,
            ];
            $n++;
        }

        return $data;
    }

    public function getOrder(Context $context, $salesChannelId, $orderNumber)
    {
        $data = [];

        $context->setConsiderInheritance(true);
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderNumber', $orderNumber));
        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }
        $criteria->addAssociation('stateMachineState');
        $criteria->addAssociation('orderCustomer.salutation');
        $criteria->addAssociation('currency');
        $criteria->addAssociation('addresses');
        $criteria->addAssociation('addresses.country');
        $criteria->addAssociation('addresses.salutation');
        $criteria->addAssociation('transactions.paymentMethod');
        $criteria->addAssociation('transactions.stateMachineState');
        $criteria->addAssociation('deliveries.shippingMethod');
        $criteria->addAssociation('deliveries.stateMachineState');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('lineItems.product');
        $criteria->addAssociation('lineItems.product.cover');
        $criteria->addAssociation('lineItems.product.options');

        $criteria->getAssociation('lineItems')->addFilter(
            new NotFilter(NotFilter::CONNECTION_OR, [
                new EqualsFilter('type', 'customized-products')
            ])
        );

        $criteria->getAssociation('lineItems')
            ->addSorting(new FieldSorting('createdAt'));

        $criteria->getAssociation('lineItems')
            ->addSorting(new FieldSorting('position'));

        $criteria->getAssociation('transactions')
            ->addSorting(new FieldSorting('updatedAt'))
            ->addSorting(new FieldSorting('createdAt'));

        $criteria->getAssociation('deliveries')
            ->addSorting(new FieldSorting('updatedAt'))
            ->addSorting(new FieldSorting('createdAt'));

        $order = $this->orderRepository->search($criteria, $context)->getEntities()->first();
        $customer = $order->getOrderCustomer();
        $billing = $order->getAddresses()->get($order->getBillingAddressId());

        $transaction = $order->getTransactions()->last();
        $delivery = $order->getDeliveries()->last();
        $shipping = $order->getAddresses()->get($delivery->getShippingOrderAddressId());

        $details = [];
        $n = 1;
        $lineItems = $order->getLineItems();

        if($lineItems)
        {
            foreach ($order->getLineItems() as $lineItem)
            {
                $type = $lineItem->getType();
                $isCustomProduct = $type == 'customized-products' ||
                                   $type == 'customized-products-option' ||
                                   $type == 'option-values';
                $isPromotion = $type == 'promotion';

                if($isCustomProduct)
                {
                    $payloadType = '';
                    $payloadValue = '';
                    $payload = $lineItem->getPayload();

                    if(array_key_exists('type', $payload)) {
                        $payloadType = $payload['type'];
                    }
                    if($payloadType == 'colorselect' ||
                        $payloadType == 'imageselect' ||
                        $payloadType == 'select') {
                        continue;
                    }

                    if(array_key_exists('value', $payload))
                    {
                        $payloadValue = $payload['value'];
                        if(is_array($payloadValue) && array_key_exists('_value', $payloadValue)) {
                            $payloadValue = $payloadValue['_value'];
                        }
                    }
                    $payloadValue = strip_tags((string) $payloadValue); // for custom products type html

                    $label = '* ' . $lineItem->getLabel() ?? '';

                    $details[] = [
                        'quantity'      => $lineItem->getQuantity(),
                        'price'         => $lineItem->getUnitPrice(),
                        'articleNumber' => $payloadValue,
                        'articleName'   => $label,
                        'storeLocation' => '', // not yet available in SW 6
                        'image'         => '',
                        'url'           => '',
                        'pos'           => $n
                    ];
                }
                elseif($isPromotion)
                {
                    $details[] = [
                        'quantity'      => $lineItem->getQuantity(),
                        'price'         => $lineItem->getUnitPrice(),
                        'articleNumber' => $lineItem->getReferencedId() ?? '',
                        'articleName'   => $lineItem->getLabel() ?? '',
                        'storeLocation' => '',
                        'image'         => '',
                        'url'           => '',
                        'pos'           => $n
                    ];
                }

                else { // normal products
                    $product = $lineItem->getProduct();
                    if($product) {
                        $imageUrl = '';
                        if($product->getCover() && $product->getCover()->getMedia()->getThumbnails()) {
                            $imageUrl = $product->getCover()->getMedia()->getThumbnails()->first() ?
                                $product->getCover()->getMedia()->getThumbnails()->first()->getUrl() :
                                $product->getCover()->getMedia()->getUrl();
                        }

                        $url = $this->generateUrl(
                            'frontend.detail.page',
                            [
                                'productId' => $product->getId()
                            ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );

                        $articleName = $product->getTranslated()['name'];
                        $options = '';
                        foreach($product->getOptions() as $option) {
                            if($options != '') {
                                $options .= ', ';
                            }
                            $options .= $option->getTranslated()['name'];
                        }
                        if($options != '') {
                            $articleName .= ' (' . $options . ')';
                        }

                        $details[] = [
                            'quantity'      => $lineItem->getQuantity(),
                            'price'         => $lineItem->getUnitPrice(),
                            'articleNumber' => $product->getProductNumber(),
                            'articleName'   => $articleName,
                            'storeLocation' => '', // not yet available in SW 6
                            'image'         => $imageUrl,
                            'url'           => $url,
                            'pos'           => $n
                        ];
                    }
                }

                $n++;
            }
        }

        $trackingCodes = '';
        if($delivery) {
            $trackingCodes = implode(', ', $delivery->getTrackingCodes());
        }

        $data[] = [
            'id'             => $order->getId(),
            'number'         => $order->getOrderNumber(),
            'status'         => 0,
            'cleared'        => 0,
            'invoiceAmount'  => $order->getAmountTotal(),
            'orderTime'      => $order->getOrderDateTime()->format('c'),
            'currency'       => $order->getCurrency()->getIsoCode(),
            'partnerId'      => $order->getAffiliateCode(),
            'customerComment'=> $order->getCustomerComment(),
            'trackingCode'   => $trackingCodes,

            'customer'       => [
                'id'            => $customer->getCustomerId(),
                'number'        => $customer->getCustomerNumber(),
                'firstname'     => $customer->getFirstname(),
                'lastname'      => $customer->getLastname(),
                'email'         => $customer->getEmail(),
                'salutation'    => $customer->getSalutation()?->getSalutationKey() ?? '',
                'title'         => $customer->getTitle() ?? '',
                'birthday'      => '',
                'internalcomment'=>'',

                'shipping'      => [
                    'company'       => $shipping->getCompany() ?? '',
                    'department'    => $shipping->getDepartment() ?? '',
                    'salutation'    => $shipping->getSalutation()?->getSalutationKey() ?? '',
                    'title'         => $shipping->getTitle() ?? '',
                    'firstname'     => $shipping->getFirstName() ?? '',
                    'lastname'      => $shipping->getLastName() ?? '',
                    'street'        => $shipping->getStreet() ?? '',
                    'zipcode'       => $shipping->getZipcode() ?? '',
                    'city'          => $shipping->getCity() ?? '',
                    'country'       => [
                        'Name'      => $shipping?->getCountry()?->getName() ?? '',
                        'IsoName'   => '', // not used in SW 6
                        'Iso'       => $shipping->getCountry()->getIso(),
                        'Iso3'      => $shipping->getCountry()->getIso3(),
                    ],
                    'phone'                  => $shipping->getPhoneNumber() ?? '',
                    'additionalAddressLine1' => $shipping->getAdditionalAddressLine1() ?? '',
                    'additionalAddressLine2' => $shipping->getAdditionalAddressLine2() ?? ''
                ],
            ],

            'billing'        => [
                'company'       => $billing->getCompany() ?? '',
                'department'    => $billing->getDepartment() ?? '',
                'salutation'    => $billing->getSalutation()?->getSalutationKey() ?? '',
                'title'         => $billing->getTitle() ?? '',
                'firstname'     => $billing->getFirstName() ?? '',
                'lastname'      => $billing->getLastName() ?? '',
                'street'        => $billing->getStreet() ?? '',
                'zipcode'       => $billing->getZipcode() ?? '',
                'city'          => $billing->getCity() ?? '',
                'country'       => [
                    'Name'      => $billing?->getCountry()?->getName() ?? '',
                    'IsoName'   => '', // not used in SW 6
                    'Iso'       => $billing->getCountry()->getIso(),
                    'Iso3'      => $billing->getCountry()->getIso3(),
                ],
                'phone'                  => $billing->getPhoneNumber() ?? '',
                'additionalAddressLine1' => $billing->getAdditionalAddressLine1() ?? '',
                'additionalAddressLine2' => $billing->getAdditionalAddressLine2() ?? ''
            ],

            'payment'        => [
                'name'          => $transaction?->getPaymentMethod()?->getName() ?? '',
                'description'   => $transaction?->getPaymentMethod()?->getDescription() ?? ''
            ],
            'paymentStatus'  => [
                'name'          => $transaction?->getStateMachineState()?->getTechnicalName() ?? '',
                'description'   => $transaction?->getStateMachineState()?->getName() ?? ''
            ],

            'dispatch'       => [
                'name'          => $delivery?->getShippingMethod()?->getName() ?? '',
                'description'   => $delivery?->getShippingMethod()?->getDescription() ?? ''
            ],
            'orderStatus'    => [
                'name'          => $order->getStateMachineState()->getTechnicalName(),
                'description'   => $order->getStateMachineState()->getName()
            ],

            'deliveryStatus' => [
                'name'          => $delivery?->getStateMachineState()?->getTechnicalName() ?? '',
                'description'   => $delivery?->getStateMachineState()?->getName() ?? ''
            ],

            'details'        => $details,
            'pos'            => 0,
        ];

        return $data;
    }

    public function getCustomer(Context $context, $salesChannelId, $customerNumber)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerNumber', $customerNumber));
        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }
        $criteria->addAssociation('salutation');
        $criteria->addAssociation('defaultBillingAddress');
        $criteria->addAssociation('defaultBillingAddress.country');
        $criteria->addAssociation('defaultBillingAddress.salutation');
        $criteria->addAssociation('defaultShippingAddress');
        $criteria->addAssociation('defaultShippingAddress.country');
        $criteria->addAssociation('defaultShippingAddress.salutation');


        $customer = $this->customerRepository->search($criteria, $context)->getEntities()->first();
        $billing = $customer->getDefaultBillingAddress();
        $shipping = $customer->getDefaultShippingAddress();

        $data = [
            'id'            => $customer->getId(),
            'number'        => $customer->getCustomerNumber(),
            'firstname'     => $customer->getFirstname(),
            'lastname'      => $customer->getLastname(),
            'email'         => $customer->getEmail(),
            'salutation'    => $customer->getSalutation()?->getSalutationKey() ?? '',
            'title'         => $customer->getTitle() ?? '',
            'birthday'      => $customer->getBirthday() ? $customer->getBirthday()->format('c') : '',
            'internalcomment'=>'',


            'shipping'      => [
                'company'                => $shipping->getCompany() ?? '',
                'department'             => $shipping->getDepartment() ?? '',
                'salutation'             => $shipping->getSalutation()?->getSalutationKey() ?? '',
                'title'                  => $shipping->getTitle() ?? '',
                'firstname'              => $shipping->getFirstName() ?? '',
                'lastname'               => $shipping->getLastName() ?? '',
                'street'                 => $shipping->getStreet() ?? '',
                'zipcode'                => $shipping->getZipcode() ?? '',
                'city'                   => $shipping->getCity() ?? '',
                'country'                => [
                    'Name'      => $shipping?->getCountry()?->getName() ?? '',
                    'IsoName'   => '', // not used in SW 6
                    'Iso'       => $shipping->getCountry()->getIso(),
                    'Iso3'      => $shipping->getCountry()->getIso3(),
                ],
                'phone'                  => $shipping->getPhoneNumber() ?? '',
                'additionalAddressLine1' => $shipping->getAdditionalAddressLine1() ?? '',
                'additionalAddressLine2' => $shipping->getAdditionalAddressLine2() ?? ''
            ],

            'billing'        => [
                'company'                => $billing->getCompany() ?? '',
                'department'             => $billing->getDepartment() ?? '',
                'salutation'             => $billing->getSalutation()?->getSalutationKey() ?? '',
                'title'                  => $billing->getTitle() ?? '',
                'firstname'              => $billing->getFirstName() ?? '',
                'lastname'               => $billing->getLastName() ?? '',
                'street'                 => $billing->getStreet() ?? '',
                'zipcode'                => $billing->getZipcode() ?? '',
                'city'                   => $billing->getCity() ?? '',
                'country'                => [
                    'Name'      => $billing?->getCountry()?->getName() ?? '',
                    'IsoName'   => '', // not used in SW 6
                    'Iso'       => $billing->getCountry()->getIso(),
                    'Iso3'      => $billing->getCountry()->getIso3(),
                ],
                'phone'                  => $billing->getPhoneNumber() ?? '',
                'additionalAddressLine1' => $billing->getAdditionalAddressLine1() ?? '',
                'additionalAddressLine2' => $billing->getAdditionalAddressLine2() ?? ''
            ],
        ];

        return $data;
    }

    public function getArticles(Context $context, $salesChannelId, $query, $searchArticleNumber)
    {
        if($query == '') {
            return [];
        }

        $context->setConsiderInheritance(true);

        $criteria = new Criteria();
        $criteria->addAssociation('cover');
        $criteria->addAssociation('options');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('deliveryTime');

        $criteria->addSorting(new FieldSorting('options.group.position'));
        $criteria->addSorting(new FieldSorting('options.position'));

        if($searchArticleNumber)
        {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('productNumber', $query),
                new EqualsFilter('manufacturerNumber', $query),
                new EqualsFilter('ean', $query),
            ]));
        }
        else
        {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new ContainsFilter('name', $query),
                new ContainsFilter('description', $query),
                new ContainsFilter('productNumber', $query),
                new ContainsFilter('manufacturerNumber', $query),
                new ContainsFilter('ean', $query),
            ]));
        }
        $products = $this->productRepository->search($criteria, $context)->getEntities();

        $data = [];
        foreach ($products as $product) {
            $tmp = [];

            $articleName = $product->getTranslation('name');
            $options = $product->getOptions();
            $optionsText = '';
            foreach($options as $option) {
                $optionsText .= ($optionsText != '' ? ' ' : '') . $option->getName();
            }
            if($optionsText != '') {
                $articleName .= ' - ' . $optionsText;
            }

            $productPrice = $product->getPrice()->first() ?
                $product->getPrice()->first()->getNet() :
                0;
            $productPseudoPrice = $product->getPrice()->first()->getListPrice() ?
                $product->getPrice()->first()->getListPrice()->getNet() :
                0;

            $shippingTime = $product->getDeliveryTime() ?
                $product->getDeliveryTime()->getName() :
                '';

            $tmp['articleId'] = $product->getId();
            $tmp['articleNumber'] = $product->getProductNumber();
            $tmp['supplierNumber'] = $product->getManufacturerNumber();
            $tmp['ean'] = $product->getEan();
            $tmp['articleName'] = $articleName;
            $tmp['price'] = $productPrice;
            $tmp['pseudoprice'] = $productPseudoPrice;
            $tmp['tax'] = $product->getTax()->getTaxRate();
            $tmp['active'] = $product->getActive();
            $tmp['highlight'] = $product->getMarkAsTopseller();
            $tmp['laststock'] = $product->getIsCloseout();
            $tmp['shippingfree'] = $product->getShippingFree();
            $tmp['instock'] = $product->getStock();
            $tmp['shippingtime'] = $shippingTime;

            $imageUrl = '';

            if($product->getCover() && $product->getCover()->getMedia()->getThumbnails()) {
                $imageUrl = $product->getCover()->getMedia()->getThumbnails()->first() ?
                    $product->getCover()->getMedia()->getThumbnails()->first()->getUrl() :
                    $product->getCover()->getMedia()->getUrl();
            }

            $url = $this->generateUrl(
                'frontend.detail.page',
                [
                    'productId' => $product->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $tmp['image'] = $imageUrl;
            $tmp['url'] = $url;

            $data[] = $tmp;
        }

        return $data;
    }

    public function updateArticle(Context $context, $salesChannelId, $params)
    {
        if(empty($params->articleid)) {
            return [];
        }

        $articleId = $params->articleid;

        if(self::FEATURE_EDIT_ARTICLES) {
            $this->productRepository->update([
                [
                    'id' => $articleId,
                    'active' => (bool)$params->active,
                    'markAsTopseller' => (bool)$params->highlight,
                    'isCloseout' => (bool)$params->laststock,
                    'stock' => (int)$params->instock,
                    'shippingFree' => (bool)$params->shippingfree,
                ]
            ], $context
            );
        }
        else {
            $this->productRepository->update([
                [
                    'id' => $articleId,
                    'stock' => (int)$params->instock,
                ]
            ], $context
            );
        }

        return [];
    }

    public function getTopArticles(Context $context, $salesChannelId, $range, $sortMode, $listLimit = 50)
    {
        [$from, $to] = $this->getDatesFromRange($range);

        $query = $this->connection->createQueryBuilder();
        $query->select([
            'HEX(p.id) as articleId',
            'p.product_number as articleNumber',
            'sum(li.quantity) as totalCount',
            'round(sum(li.total_price), 0) as totalAmount',
        ]);
        $query->from('`order`', 'o');

        $query->leftJoin(
            'o', 'order_line_item', 'li',
            'o.id = li.order_id AND o.version_id = li.order_version_id'
        );

        $query->leftJoin(
            'li', 'product', 'p',
            'li.product_id = p.id AND li.product_version_id = p.version_id'
        );

        $query->groupBy('p.product_number');

        $query->where('p.id IS NOT NULL');

        // custom products machen Probleme und haben nicht wirklich Aussagekraft
        $query->andWhere('li.parent_id IS NULL');

        $query->andWhere('o.order_date_time >= :from');
        $query->andWhere('o.order_date_time <= :to');
        $query->andWhere('o.version_id = :liveVersionId');

        $this->addStateMachineFilter($query, $salesChannelId);

        $query->setMaxResults($listLimit);

        $query->setParameter('from', $from);
        $query->setParameter('to', $to);
        $query->setParameter('liveVersionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));

        if($salesChannelId != '') {
            $query->andWhere('o.sales_channel_id = :salesChannelId');
            $query->setParameter('salesChannelId', Uuid::fromHexToBytes($salesChannelId));
        }

        if ($sortMode) {
            $query->addOrderBy('totalCount', 'DESC');
            $query->addOrderBy('totalAmount', 'DESC');
        }
        else {
            $query->addOrderBy('totalAmount', 'DESC');
            $query->addOrderBy('totalCount', 'DESC');
        }
        $p = $query->executeQuery()->fetchAllAssociative();

        $context->setConsiderInheritance(true);

        $pos = 1;
        foreach ($p as &$product)
        {
            $criteria = new Criteria();
            $criteria->addAssociation('cover');
            $criteria->addAssociation('options');
            $criteria->addFilter(new EqualsFilter('id', $product['articleId']));
            $pe = $this->productRepository->search($criteria, $context)->first();

            if($pe)
            {
                $imageUrl = '';
                if($pe->getCover() && $pe->getCover()->getMedia()->getThumbnails()) {
                    $imageUrl = $pe->getCover()->getMedia()->getThumbnails()->first() ?
                        $pe->getCover()->getMedia()->getThumbnails()->first()->getUrl() :
                        $pe->getCover()->getMedia()->getUrl();
                }
                $url = $this->generateUrl('frontend.detail.page', [
                    'productId' => $pe->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $options = '';
                foreach($pe->getOptions() as $option) {
                    if($options != '') $options .= ', ';
                    $options .= $option->getTranslated()['name'];
                }
                $product['articleName'] = $pe->getTranslated()['name'] .
                    ($options != '' ? ' ('.$options.')' : '');
                $product['image'] = $imageUrl;
                $product['url'] = $url;
                $product['pos'] = $pos;
                $pos++;
            }
        }

        return $p;
    }

    public function getTopCustomers(Context $context, $salesChannelId, $range, $listLimit = 50)
    {
        $config = $this->config->get('NetzpShopmanager6.config');
        $priceMode = $config['pricemode'];
        $amountColumn = $priceMode == self::PRICEMODE_NET ? 'amount_net' : 'amount_total';

        $data = [];
        [$from, $to] = $this->getDatesFromRange($range);

        $query = $this->connection->createQueryBuilder();
        $query->select([
            'c.customer_number as number',
            'concat(c.last_name, ", ", c.first_name) as name',
            'round(sum(o.' . $amountColumn . '), 2) as total',
            'count(o.id) as orders'
        ]);
        $query->from('`order`', 'o');

        $query->leftJoin(
            'o', 'order_customer', 'c',
            'c.order_id = o.id AND c.order_version_id = o.version_id'
        );

        $query->groupBy('c.customer_number');
        $query->addOrderBy('total', 'DESC');

        $query->andWhere('o.order_date_time >= :from');
        $query->andWhere('o.order_date_time <= :to');
        $query->andWhere('o.version_id = :liveVersionId');

        $this->addStateMachineFilter($query, $salesChannelId);

        $query->setMaxResults($listLimit);

        $query->setParameter('from', $from);
        $query->setParameter('to', $to);
        $query->setParameter('liveVersionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));

        if($salesChannelId != '') {
            $query->andWhere('o.sales_channel_id = :salesChannelId');
            $query->setParameter('salesChannelId', Uuid::fromHexToBytes($salesChannelId));
        }

        $customers = $query->executeQuery()->fetchAllAssociative();
        $pos = 1;

        foreach($customers as $customer) {
            $tmp = $customer;
            $tmp['groupKey'] = ''; // not available in SW6 ?
            $tmp['pos'] = $pos;
            $data[] = $tmp;
            $pos++;
        }

        return $data;
    }

    public function getTopSuppliers(Context $context, $salesChannelId, $range, $sortMode, $listLimit = 50)
    {
        $data = [];
        [$from, $to] = $this->getDatesFromRange($range);
        $languageId = $context->getLanguageIdChain()[0];

        $query = $this->connection->createQueryBuilder();
        $query->select([
            'HEX(m.id) as supplierId', 'mt.name as supplierName',
            'round(sum(li.total_price), 0) as total',
            'count(o.id) as orders'
        ]);

        $query->from('`order`', 'o', 'p', 'm');
        $query->leftJoin('o', 'order_line_item',
            'li', 'o.id = li.order_id AND o.version_id = li.order_version_id');
        $query->leftJoin('li', 'product',
            'p', 'li.product_id = p.id');
        $query->leftJoin('p', 'product_manufacturer',
            'm', 'p.manufacturer = m.id');
        $query->leftJoin('m', 'product_manufacturer_translation',
            'mt', 'mt.product_manufacturer_id = m.id AND
                                mt.product_manufacturer_version_id = m.version_id AND
                                mt.language_id = :languageId');

        $query->groupBy('mt.name');

        $query->where('p.id IS NOT NULL');

        // custom products machen Probleme und haben nicht wirklich Aussagekraft
        $query->andWhere('li.parent_id IS NULL');

        $query->andWhere('o.order_date_time >= :from');
        $query->andWhere('o.order_date_time <= :to');
        $query->andWhere('o.version_id = :liveVersionId');

        $this->addStateMachineFilter($query, $salesChannelId);
        $query->setMaxResults($listLimit);

        $query->setParameter('from', $from);
        $query->setParameter('to', $to);
        $query->setParameter('liveVersionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('languageId', Uuid::fromHexToBytes($languageId));

        if($salesChannelId != '') {
            $query->andWhere('o.sales_channel_id = :salesChannelId');
            $query->setParameter('salesChannelId', Uuid::fromHexToBytes($salesChannelId));
        }

        if ($sortMode) {
            $query->addOrderBy('orders', 'DESC');
            $query->addOrderBy('total', 'DESC');
        }
        else {
            $query->addOrderBy('total', 'DESC');
            $query->addOrderBy('orders', 'DESC');
        }

        $suppliers = $query->executeQuery()->fetchAllAssociative();
        $pos = 1;
        foreach($suppliers as $supplier) {
            $tmp['pos'] = $pos;
            $tmp['supplierId'] = $supplier['supplierId'];
            $tmp['supplierName'] = $supplier['supplierName'] ?? '---';
            $tmp['total'] = $supplier['total'];
            $tmp['orders'] = $supplier['orders'];

            $data[] = $tmp;
            $pos++;
        }

        return $data;
    }

    public function setMaintenance(Context $context, $salesChannelId, $isMaintenance = false)
    {
        $this->salesChannelRepository->update(
            [
                [ 'id' => $salesChannelId, 'maintenance' => $isMaintenance],
            ],
            $context
        );
    }

    public function setShopMessage(Context $context, $salesChannelId, $msg = '')
    {
        $this->config->set('NetzpShopmanager6.config.message', $msg, $salesChannelId);
    }

    private function getDatesFromRange($range = 'day')
    {
        $dFrom = new DateTime();
        $dTo = new DateTime();
        $from = $dFrom->format('Y-m-d 00:00:00');
        $to = $dTo->format('Y-m-d 23:59:59');

        if($range == 'today') {
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dTo->format('Y-m-d 23:59:59');
        }
        elseif($range == 'yesterday') {
            $dFrom->sub(new DateInterval('P1D'));
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dFrom->format('Y-m-d 23:59:59');
        }
        elseif($range == 'day') {
            $dFrom->sub(new DateInterval('P6D'));
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dTo->format('Y-m-d 23:59:59');
        }
        elseif($range == 'day2') {
            $dFrom->sub(new DateInterval('P29D'));
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dTo->format('Y-m-d 23:59:59');
        }
        elseif($range == 'week') {
            $dFrom->sub(new DateInterval('P8W'));
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dTo->format('Y-m-d 23:59:59');
        }
        elseif($range == 'week2') {
            $dFrom->sub(new DateInterval('P16W'));
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dTo->format('Y-m-d 23:59:59');
        }
        elseif($range == 'month') {
            $dFrom->sub(new DateInterval('P12M'));
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dTo->format('Y-m-d 23:59:59');
        }
        elseif($range == 'month2') {
            $dFrom->sub(new DateInterval('P24M'));
            $from = $dFrom->format('Y-m-d 00:00:00');
            $to = $dTo->format('Y-m-d 23:59:59');
        }
        elseif($range == 'all') {
            $from = '1970-01-01 00:00:00';
            $to = '2070-12-31 23:59:59';
        }

        return [$from, $to];
    }

    private function addStateMachineFilter($query, string $salesChannelId = null)
    {
        $query->leftJoin(
            'o', 'state_machine_state', 'orderstate',
            'orderstate.id = o.state_id'
        );

        $query->leftJoin(
            'o', 'order_transaction', 'transaction',
            'transaction.order_id = o.id AND transaction.order_version_id = o.version_id'
        );
        $query->leftJoin(
            'transaction', 'state_machine_state', 'transactionstate',
            'transaction.state_id = transactionstate.id'
        );

        $excludedTransactionStates = ['cancelled', 'failed', 'chargeback', 'refunded'];
        $excludedTransactionStates = array_map(fn($i) => chr(34) . $i . chr(34), $excludedTransactionStates);
        $excludedTransactionStates = implode(',', $excludedTransactionStates);

        $query->andWhere('orderstate.technical_name <> "cancelled"');
        $query->andWhere('transactionstate.technical_name NOT IN (' .
            $excludedTransactionStates . ')');

        if($this->getConfig('onlypaid', false, $salesChannelId))
        {
            $query->andWhere('transactionstate.technical_name = "paid"');
        }
    }

    private function generateUrl($route, $parameters, $referenceType)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    public function getConfig(string $key, mixed $defaultValue = null, string $salesChannelId = null)
    {
        $config = $this->config->get(
            'NetzpShopmanager6.config',
            $salesChannelId != null && $salesChannelId != ''
                ? $salesChannelId
                : null
        );

        return array_key_exists($key, $config) ? $config[$key] : $defaultValue;
    }
}
