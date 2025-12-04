<?php declare(strict_types=1);

namespace Swag\AmazonPay\Installer;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\RestrictDeleteViolationException;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\Currency\Rule\CurrencyRule;

readonly class RuleInstaller
{
    public const VALID_CURRENCIES = [
        'GBP', // British Pound
        'EUR', // Euro
        'AUD', // Australian Dollar
        'DKK', // Danish Krone
        'HKD', // Hong Kong Dollar
        'JPY', // Japanese Yen
        'NZD', // New Zealand Dollar
        'NOK', // Norwegian Krone
        'ZAR', // South African Rand
        'SEK', // Swedish Krone
        'CHF', // Swiss Franc
        'USD', // United States Dollar
    ];

    public const RULE_ID = 'e44e6782c64247b796b891cf25ae38f7';
    public const CONDITION_ID = '30bf136c5c4c4677adfbb48fe165640e';

    public function __construct(
        private EntityRepository $ruleRepository,
        private EntityRepository $currencyRepository,
        private EntityRepository $paymentMethodRepository
    ) {
    }

    public function install(InstallContext $context): void
    {
        $this->upsertAvailabilityRule($context->getContext());
    }

    public function update(UpdateContext $context): void
    {
        $this->upsertAvailabilityRule($context->getContext());
    }

    public function uninstall(UninstallContext $context): void
    {
        $this->removeAvailabilityRule($context->getContext());
    }

    private function upsertAvailabilityRule(Context $context): void
    {
        $data = [
            'id' => self::RULE_ID,
            'name' => 'AmazonPayAvailabilityRule',
            'priority' => 1,
            'description' => 'Determines whether or not Amazon Pay is available.',
            'conditions' => [
                [
                    'id' => self::CONDITION_ID,
                    'type' => (new CurrencyRule())->getName(),
                    'value' => [
                        'operator' => Rule::OPERATOR_EQ,
                        'currencyIds' => \array_values($this->getCurrencies($context)),
                    ],
                ],
            ],
            'paymentMethods' => [
                ['id' => PaymentMethodInstaller::AMAZON_PAYMENT_ID], ],
        ];

        $this->ruleRepository->upsert([$data], $context);
    }

    private function removeAvailabilityRule(Context $context): void
    {
        $this->paymentMethodRepository->update([
            [
                'id' => PaymentMethodInstaller::AMAZON_PAYMENT_ID,
                'availabilityRuleId' => null,
            ],
        ], $context);

        $deletion = [
            'id' => self::RULE_ID,
        ];

        try {
            $this->ruleRepository->delete([$deletion], $context);
        } catch (RestrictDeleteViolationException $e) {
            // Rule is in use and cannot be deleted
        }
    }

    private function getCurrencies(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter('isoCode', self::VALID_CURRENCIES)
        );

        return $this->currencyRepository->searchIds($criteria, $context)->getIds();
    }
}
