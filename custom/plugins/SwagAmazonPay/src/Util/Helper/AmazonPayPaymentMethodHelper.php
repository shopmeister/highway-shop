<?php declare(strict_types=1);

namespace Swag\AmazonPay\Util\Helper;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;

readonly class AmazonPayPaymentMethodHelper implements AmazonPayPaymentMethodHelperInterface
{
    public function __construct(private EntityRepository $salesChannelRepository)
    {
    }

    public function isAmazonPayActive(SalesChannelContext $context): bool
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_AND,
                [
                    new EqualsFilter('id', $context->getSalesChannel()->getId()),
                    new EqualsFilter('paymentMethods.id', PaymentMethodInstaller::AMAZON_PAYMENT_ID),
                    new EqualsFilter('paymentMethods.active', true),
                ]
            )
        );

        return $this->salesChannelRepository->searchIds($criteria, $context->getContext())->firstId() !== null;
    }

    public function setSalesChannelDefault(?string $salesChannelId, Context $context): void
    {
        $salesChannelsToChange = $this->getSalesChannelsToChange($salesChannelId, $context);
        $updateData = [];

        foreach ($salesChannelsToChange as $salesChannel) {
            $salesChannelUpdateData = [
                'id' => $salesChannel->getId(),
                'paymentMethodId' => PaymentMethodInstaller::AMAZON_PAYMENT_ID,
            ];

            $paymentMethodCollection = $salesChannel->getPaymentMethods();
            if ($paymentMethodCollection === null || $paymentMethodCollection->get(PaymentMethodInstaller::AMAZON_PAYMENT_ID) === null) {
                $salesChannelUpdateData['paymentMethods'][] = [
                    'id' => PaymentMethodInstaller::AMAZON_PAYMENT_ID,
                ];
            }

            $updateData[] = $salesChannelUpdateData;
        }

        if ($updateData === []) {
            return;
        }

        $this->salesChannelRepository->update($updateData, $context);
    }

    private function getSalesChannelsToChange(?string $salesChannelId, Context $context): SalesChannelCollection
    {
        $criteria = new Criteria();

        if ($salesChannelId !== null) {
            $criteria->setIds([$salesChannelId]);
        } else {
            $criteria->addFilter(
                new EqualsAnyFilter('typeId', [
                    Defaults::SALES_CHANNEL_TYPE_STOREFRONT,
                    Defaults::SALES_CHANNEL_TYPE_API,
                ])
            );
        }

        $criteria->addAssociation('paymentMethods');

        /** @var SalesChannelCollection $collection */
        $collection = $this->salesChannelRepository->search($criteria, $context)->getEntities();

        return $collection;
    }
}
