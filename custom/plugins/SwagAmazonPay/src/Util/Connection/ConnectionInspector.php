<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util\Connection;

use Amazon\Pay\API\ClientInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Client\Hydrator\Request\CreateCheckoutSession\CreateCheckoutSessionHydrator;
use Swag\AmazonPay\Components\Client\Hydrator\Request\CreateCheckoutSession\CreateCheckoutSessionHydratorInterface;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Hydrator\ConfigHydratorInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\Config\Validation\ConfigValidatorInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Util\Connection\Exception\CreateSessionException;
use Swag\AmazonPay\Util\Connection\Exception\InitializeClientException;
use Swag\AmazonPay\Util\Connection\Exception\InvalidKeyException;
use Swag\AmazonPay\Util\Connection\Exception\InvalidProtocolException;
use Swag\AmazonPay\Util\Connection\Exception\InvalidSessionIdException;
use Swag\AmazonPay\Util\Connection\Exception\ObtainSessionException;

class ConnectionInspector implements ConnectionInspectorInterface
{
    private ClientProviderInterface $clientProvider;

    private ClientInterface $apiClient;

    private CreateCheckoutSessionHydratorInterface $createCheckoutSessionHydrator;

    private ConfigValidatorInterface $configValidator;

    private AmazonPayConfigStruct $pluginConfig;

    private ConfigHydratorInterface $configHydrator;

    private LoggerInterface $logger;

    public function __construct(
        ClientProviderInterface $clientProvider,
        CreateCheckoutSessionHydrator $createCheckoutSessionHydrator,
        ConfigValidatorInterface $configValidator,
        ConfigHydratorInterface $configHydrator,
        LoggerInterface $logger
    ) {
        $this->clientProvider = $clientProvider;
        $this->createCheckoutSessionHydrator = $createCheckoutSessionHydrator;
        $this->configValidator = $configValidator;
        $this->configHydrator = $configHydrator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function test(array $credentials): array
    {
        $isSuccess = false;
        $exceptionMessage = '';
        $message = 'swag-amazon-pay-configuration.notification.inspectConnectionSuccess';

        try {
            $this->inspectCredentials($credentials);
            $this->validatePrivateKey();
            $this->initializeClient();
            $checkoutSessionId = $this->createSession();
            $this->obtainSession($checkoutSessionId);

            $isSuccess = true;
        } catch (ConfigValidationException $e) {
            $message = 'swag-amazon-pay-configuration.exception.inspectConnection.credentialsNotFound';
        } catch (InvalidKeyException $e) {
            $message = 'swag-amazon-pay-configuration.exception.inspectConnection.privateKeyInvalid';
        } catch (InvalidProtocolException $e) {
            $message = 'swag-amazon-pay-configuration.exception.inspectConnection.invalidProtocol';
        } catch (InitializeClientException $e) {
            $message = 'swag-amazon-pay-configuration.exception.inspectConnection.initialisingClientError';
            $exceptionMessage = $e->getMessage();
        } catch (CreateSessionException|ObtainSessionException $e) {
            $message = 'swag-amazon-pay-configuration.exception.inspectConnection.createSessionError';
            $exceptionMessage = $e->getMessage();
        }

        if ($isSuccess !== true) {
            $this->logger->error('Amazon Pay API credentials check failed', [
                'exception' => isset($e) ? $e->getMessage() : 'Unknown error',
                'displayMessage' => $message,
            ]);
        }

        return [
            'success' => $isSuccess,
            'message' => $message,
            'exceptionMessage' => $exceptionMessage,
        ];
    }

    /**
     * @throws ConfigValidationException
     */
    public function inspectCredentials(array $credentials): void
    {
        $config = \array_merge(ConfigServiceInterface::DEFAULT_CONFIG, $credentials);
        $this->configValidator->validate($config);

        $this->pluginConfig = $this->configHydrator->hydrate($config);
    }

    /**
     * @throws ConfigValidationException
     * @throws InvalidKeyException
     */
    public function validatePrivateKey(): void
    {
        if ((\mb_strpos($this->pluginConfig->getPrivateKey(), 'BEGIN RSA PRIVATE KEY') === false) && (\mb_strpos($this->pluginConfig->getPrivateKey(), 'BEGIN PRIVATE KEY') === false)) {
            throw new InvalidKeyException('Invalid private key configured');
        }
    }

    /**
     * @throws InitializeClientException
     */
    public function initializeClient(): void
    {
        try {
            $this->apiClient = $this->clientProvider->getLegacyClient(null, ClientProviderInterface::REGION_EU, $this->pluginConfig);
        } catch (\Exception $e) {
            throw new InitializeClientException($e->getMessage());
        }
    }

    /**
     * @throws CreateSessionException
     * @throws InvalidProtocolException
     */
    public function createSession(): string
    {
        try {
            $request = $this->createCheckoutSessionHydrator->hydrate(false, $this->pluginConfig->getClientId());
            $session = $this->apiClient->createCheckoutSession(\json_encode($request), ['x-amz-pay-idempotency-key' => Uuid::randomHex()]);
            $response = \json_decode($session['response']);
        } catch (\Throwable $e) {
            throw new CreateSessionException($e->getMessage());
        }

        if ($response === null) {
            throw new CreateSessionException('An unknown error occurred while connection Amazon Pay.');
        }

        if (\mb_strpos($session['response'], 'InvalidParameterValue') !== false && \mb_strpos($session['response'], 'The value \'http:') !== false) {
            throw new InvalidProtocolException($session['response']);
        }

        if (\property_exists($response, 'checkoutSessionId') === false || empty($response->checkoutSessionId)) {
            throw new CreateSessionException($session['response']);
        }

        return $response->checkoutSessionId;
    }

    /**
     * @throws ObtainSessionException
     */
    public function obtainSession(string $checkoutSessionId): void
    {
        $session = [];

        try {
            $session = $this->apiClient->getCheckoutSession($checkoutSessionId);
            $response = \json_decode($session['response']);

            if ($checkoutSessionId !== $response->checkoutSessionId) {
                throw new InvalidSessionIdException('SessionId from response does not match');
            }
        } catch (\Exception $e) {
            if (\array_key_exists('response', $session)) {
                throw new ObtainSessionException($session['response']);
            }

            throw new ObtainSessionException($e->getMessage());
        }
    }
}
