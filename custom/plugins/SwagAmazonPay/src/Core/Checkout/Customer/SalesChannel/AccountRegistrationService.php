<?php declare(strict_types=1);
namespace Swag\AmazonPay\Core\Checkout\Customer\SalesChannel;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractRegisterRoute;
use Shopware\Core\Content\Newsletter\Exception\SalesChannelDomainNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Replaces Shopware\Core\Checkout\Customer\SalesChannel\AccountRegistrationService
 *
 * @internal
 */
readonly class AccountRegistrationService
{
    public function __construct(
        private EntityRepository      $domainRepository,
        private SystemConfigService   $systemConfigService,
        private AbstractRegisterRoute $registerRoute
    ) {
    }

    public function register(
        DataBag $data,
        bool $isGuest,
        SalesChannelContext $context,
        ?DataValidationDefinition $additionalValidationDefinitions = null
    ): CustomerEntity {
        return $this->registerViaRoute($isGuest, $data, $context, $additionalValidationDefinitions);
    }

    private function registerViaRoute(
        bool $isGuest,
        DataBag $data,
        SalesChannelContext $context,
        ?DataValidationDefinition $additionalValidationDefinitions
    ): CustomerEntity {
        if ($isGuest) {
            $data->set('guest', true);
        }

        if (!$data->has('storefrontUrl')) {
            $data->set('storefrontUrl', $this->getConfirmUrl($context));
        }

        return $this->registerRoute->register(
            $data->toRequestDataBag(),
            $context,
            false,
            $additionalValidationDefinitions
        )->getCustomer();
    }

    private function getConfirmUrl(SalesChannelContext $context): string
    {
        $salesChannel = $context->getSalesChannel();

        /** @var string $domainUrl */
        $domainUrl = $this->systemConfigService
            ->get('core.loginRegistration.doubleOptInDomain', $salesChannel->getId());

        if (!$domainUrl) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannel->getId()));
            $criteria->setLimit(1);

            $domain = $this->domainRepository
                ->search($criteria, $context->getContext())
                ->first();

            if (!$domain) {
                throw new SalesChannelDomainNotFoundException($salesChannel);
            }

            $domainUrl = $domain->getUrl();
        }

        return $domainUrl;
    }
}
