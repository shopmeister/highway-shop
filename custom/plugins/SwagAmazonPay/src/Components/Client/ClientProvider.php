<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client;

use Amazon\Pay\API\ClientInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Util\Config\VersionProviderInterface;

class ClientProvider implements ClientProviderInterface
{
    private ConfigServiceInterface $configService;

    private LoggerInterface $logger;
    private VersionProviderInterface $versionProvider;

    public function __construct(
        ConfigServiceInterface   $configService,
        VersionProviderInterface $versionProvider,
        LoggerInterface          $logger
    )
    {
        $this->configService = $configService;
        $this->logger = $logger;
        $this->versionProvider = $versionProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConfigValidationException
     * @throws \Exception
     */
    public function getLegacyClient(?string $salesChannelId = null, ?string $region = null, ?AmazonPayConfigStruct $config = null): ClientInterface
    {
        if ($config === null) {
            $config = $this->configService->getPluginConfig($salesChannelId);
        }

        if($region === null){
            if($config->getLedgerCurrency() === AmazonPayConfigStruct::LEDGER_CURRENCY_US){
                $region = self::REGION_US;
            }else {
                $region = self::REGION_EU;
            }
        }

        $versions = $this->versionProvider->getVersions();

        $client = new LegacyClient([
            'public_key_id' => $config->getPublicKeyId(),
            'private_key' => $config->getPrivateKey(),
            'sandbox' => $config->isSandboxActive(),
            'region' => $region,
            'integrator_id' => ConfigServiceInterface::PLATFORM_ID,
            'integrator_version' => $versions['plugin'],
            'platform_version' => $versions['shopware'],
        ]);

        $client->setLogger($this->logger);

        return $client;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConfigValidationException
     * @throws \Exception
     */
    public function getClient(?string $salesChannelId = null, ?string $region = null, ?AmazonPayConfigStruct $config = null): \AmazonPayApiSdkExtension\Client\Client
    {
        if ($config === null) {
            $config = $this->configService->getPluginConfig($salesChannelId);
        }

        if($region === null){
            if($config->getLedgerCurrency() === AmazonPayConfigStruct::LEDGER_CURRENCY_US){
                $region = self::REGION_US;
            }else {
                $region = self::REGION_EU;
            }
        }

        $versions = $this->versionProvider->getVersions();

        $client = new Client([
            'public_key_id' => $config->getPublicKeyId(),
            'private_key' => $config->getPrivateKey(),
            'sandbox' => $config->isSandboxActive(),
            'region' => $region,
            'integrator_id' => ConfigServiceInterface::PLATFORM_ID,
            'integrator_version' => $versions['plugin'],
            'platform_version' => $versions['shopware'],
        ]);

        $client->setLogger($this->logger);

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return ['x-amz-pay-idempotency-key' => Uuid::randomHex()];
    }
}
