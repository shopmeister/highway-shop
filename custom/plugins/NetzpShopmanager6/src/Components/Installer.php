<?php declare(strict_types=1);

namespace NetzpShopmanager6\Components;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Shopware\Core\System\Integration\IntegrationDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;

class Installer
{
    final public const INTEGRATION_ID     = '5DA1E1D3A24F490395AC923C739EA8A1';
    final public const CUSTOM_FIELDSET_ID = '3056FC90A4A845EDB598DFB8F0BDA6B2';

    public function __construct(private readonly ContainerInterface $container) {
    }

    public function install()
    {
        try {
            $this->createCustomfields();
        }
        catch (\Exception) {
            //
        }

        try {
            $this->createAccessKey();
        }
        catch (\Exception) {
            //
        }
    }

    private function createAccessKey()
    {
        $key = AccessKeyHelper::generateAccessKey('integration');
        $secret = AccessKeyHelper::generateSecretAccessKey();

        $data = [
            'id'               => strtolower(self::INTEGRATION_ID),
            'label'            => 'Shopmanager for Shopware',
            'accessKey'        => $key,
            'secretAccessKey'  => password_hash($secret, PASSWORD_BCRYPT),
            'writeAccess'      => true,
            'admin'            => true,
            'customFields'     => [
                'netzp_shopmanager_type' => 'sm',
                'netzp_shopmanager_key'  => $secret
            ],
        ];

        $repo = $this->container->get('integration.repository');
        $repo->create([$data], new Context(new SystemSource()));
    }

    private function createCustomfields()
    {
        $customFieldsRepository = $this->container->get('custom_field_set.repository');

        $customFieldsRepository->upsert([[
            'id'     => strtolower(self::CUSTOM_FIELDSET_ID),
            'name'   => 'netzp_shopmanager6',

            'config' => [
                'label' => [
                    'en-GB' => 'Shopmanager App',
                    'de-DE' => 'Shopmanager App'
                ]
            ],

            'customFields' => [
                [
                    'id'     => Uuid::randomHex(),
                    'name'   => 'netzp_shopmanager_type',
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'customFieldPosition' => 1,
                        'label' => [
                            'en-GB' => 'Shopmanager App Typ',
                            'de-DE' => 'Shopmanager app type'
                        ]
                    ]
                ],
                [
                    'id'     => Uuid::randomHex(),
                    'name'   => 'netzp_shopmanager_key',
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'customFieldPosition' => 2,
                        'label' => [
                            'en-GB' => 'Shopmanager App Key',
                            'de-DE' => 'Shopmanager app key'
                        ]
                    ]
                ]
            ],

            'relations' => [
                [
                    'id' => strtolower(self::CUSTOM_FIELDSET_ID),
                    'entityName' => $this->container->get(IntegrationDefinition::class)->getEntityName()
                ]
            ]
        ]], new Context(new SystemSource()));
    }
}
