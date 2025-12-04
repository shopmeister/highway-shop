<?php declare(strict_types=1);

namespace Swag\AmazonPay\Installer;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

readonly class CustomFieldsInstaller implements InstallerInterface
{
    public const CUSTOM_FIELD_NAME_ACCOUNT_ID = 'swag_amazon_pay_account_id';
    public const CUSTOM_FIELD_NAME_CHECKOUT_ID = 'swag_amazon_pay_checkout_id';
    public const CUSTOM_FIELD_NAME_CHARGE_ID = 'swag_amazon_pay_charge_id';
    public const CUSTOM_FIELD_NAME_LAST_REFUND_ID = 'swag_amazon_pay_last_refund_id';
    public const CUSTOM_FIELD_NAME_CHARGE_PERMISSION_ID = 'swag_amazon_pay_charge_permission_id';
    public const CUSTOM_FIELD_NAME_ERROR_REASON_CODE = 'swag_amazon_pay_error_reason_code';
    public const CUSTOM_FIELD_NAME_ERROR_REASON_DESCRIPTION = 'swag_amazon_pay_error_reason_description';

    private const CUSTOM_FIELDSETS = [
        [
            'id' => '47ea311350234ac93760d783d67cc572',
            'name' => 'swag_amazon_pay',
            'config' => [
                'label' => [
                    'en-GB' => 'Amazon Pay',
                    'de-DE' => 'Amazon Pay',
                ],
            ],
            'customFields' => [
                [
                    'id' => '13a00ec87d6123e2f36c49bc9d290bfc',
                    'name' => self::CUSTOM_FIELD_NAME_CHARGE_ID,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Amazon Pay Charge ID',
                            'de-DE' => 'Amazon Pay Einzugs-ID',
                        ],
                    ],
                ],
                [
                    'id' => '3dc53a7ced664dca8d3c727c358ad03e',
                    'name' => self::CUSTOM_FIELD_NAME_LAST_REFUND_ID,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Amazon Pay Refund ID',
                            'de-DE' => 'Amazon Pay Refund-ID',
                        ],
                    ],
                ],
                [
                    'id' => '4634a6e7a2d9709519196b72239a5df3',
                    'name' => self::CUSTOM_FIELD_NAME_CHARGE_PERMISSION_ID,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Amazon Pay Charge-Permission ID',
                            'de-DE' => 'Amazon Pay Charge-Permission ID',
                        ],
                    ],
                ],
                [
                    'id' => '5143e31cb18b5d098696934742ae204f',
                    'name' => self::CUSTOM_FIELD_NAME_CHECKOUT_ID,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Amazon Pay Checkout ID',
                            'de-DE' => 'Amazon Pay Checkout-ID',
                        ],
                    ],
                ],
                [
                    'id' => '9955db2d62e990765971a65aef78cc01',
                    'name' => self::CUSTOM_FIELD_NAME_ACCOUNT_ID,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Amazon Account ID',
                            'de-DE' => 'Amazon Account-ID',
                        ],
                    ],
                ],
                [
                    'id' => 'ccfdff1d89b046f1b9d0f999feacb4c1',
                    'name' => self::CUSTOM_FIELD_NAME_ERROR_REASON_CODE,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Error reason code',
                            'de-DE' => 'Fehler Reason-Code',
                        ],
                    ],
                ],
                [
                    'id' => '4b4ee045d1084388905a2d701d2ca1ab',
                    'name' => self::CUSTOM_FIELD_NAME_ERROR_REASON_DESCRIPTION,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Error reason description',
                            'de-DE' => 'Fehler Reason-Description',
                        ],
                    ],
                ],
            ],
        ],
    ];


    public function __construct(private EntityRepository $customFieldSetRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $this->customFieldSetRepository->upsert(self::CUSTOM_FIELDSETS, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $this->customFieldSetRepository->upsert(self::CUSTOM_FIELDSETS, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $data = $this->getActivationData(false);

            $this->customFieldSetRepository->upsert($data, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $data = $this->getActivationData(true);

            $this->customFieldSetRepository->upsert($data, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $data = $this->getActivationData(false);

            $this->customFieldSetRepository->upsert($data, $context);
        });
    }

    private function getActivationData(bool $active): array
    {
        $data = self::CUSTOM_FIELDSETS;

        foreach ($data as $setKey => $set) {
            $data[$setKey]['active'] = $active;

            foreach (\array_keys($set['customFields']) as $fieldKey) {
                $data[$setKey]['customFields'][$fieldKey]['active'] = $active;
            }
        }

        return $data;
    }
}
