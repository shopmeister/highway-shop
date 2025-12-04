<?php
/**
 * Created by PhpStorm.
 * User: constantin
 * Date: 28.02.17
 * Time: 15:35
 */
namespace Dtgs\RichSnippets\Components\Helper;

use Shopware\Core\Checkout\Customer\CustomerCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class CustomerHelper
{
    /**
     * @var EntityRepository
     */
    private $customerRepository;

    public function __construct(EntityRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $customerId
     * @param $context
     * @return CustomerEntity
     */
    public function getCustomerById($customerId, $context)
    {
        $criteria = new Criteria([$customerId]);
        /** @var CustomerCollection $customercollection */
        $customercollection = $this->customerRepository->search($criteria, $context->getContext())->getEntities();
        return $customercollection->get($customerId);
    }

}
