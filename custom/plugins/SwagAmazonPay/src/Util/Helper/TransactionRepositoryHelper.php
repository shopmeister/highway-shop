<?php declare(strict_types=1);

namespace Swag\AmazonPay\Util\Helper;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Cache\CacheInterface;

class TransactionRepositoryHelper implements TransactionRepositoryHelperInterface
{
    public const CACHE_KEY = 'state-machine-initial-state-ids';
    private array $ids = [];

    public function __construct(
        //SW65
        private readonly EntityRepository $transactionRepository,
        private readonly Connection $connection,
        private readonly CacheInterface $cache
    ) {
    }



    public function getInvalidTransactions(Context $context): IdSearchResult
    {
        $yesterday = new \DateTime('now -1 day');
        $yesterday = $yesterday->setTimezone(new \DateTimeZone('UTC'));

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('paymentMethodId', PaymentMethodInstaller::AMAZON_PAYMENT_ID),
                new EqualsFilter('customFields.swag_amazon_pay_charge_permission_id', null),
                new EqualsFilter(
                    'stateMachineState.id',
                    $this->initialStateIdLoaderGet(
                        OrderTransactionStates::STATE_MACHINE
                    )
                ),
                new NotFilter(
                    'AND',
                    [
                        new EqualsFilter('customFields.swag_amazon_pay_checkout_id', null),
                    ]
                ),
                new RangeFilter(
                    'createdAt',
                    [
                        RangeFilter::LTE => $yesterday->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                ),
            ]
        ));

        return $this->transactionRepository->searchIds($criteria, $context);
    }


    protected function initialStateIdLoaderGet(string $name): string
    {
        if (!isset($this->ids[$name])) {
            $this->ids = $this->initialStateIdLoaderLoad();
        }

        return $this->ids[$name];
    }

    protected function initialStateIdLoaderLoad(): array
    {
        return $this->cache->get(self::CACHE_KEY, function () {
            return $this->connection->fetchAllKeyValue(
                'SELECT technical_name, LOWER(HEX(`initial_state_id`)) as initial_state_id FROM state_machine'
            );
        });
    }


}
