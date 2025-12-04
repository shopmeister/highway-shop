<?php declare(strict_types=1);

namespace Swag\AmazonPay\Util\Helper;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

interface TransactionRepositoryHelperInterface
{
    public function getInvalidTransactions(Context $context): IdSearchResult;
}
