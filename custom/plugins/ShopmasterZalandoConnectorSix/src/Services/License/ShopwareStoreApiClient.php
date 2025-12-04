<?php

namespace ShopmasterZalandoConnectorSix\Services\License;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Client for Shopware Store API to check license and booking status
 *
 * @see https://docs.shopware.com/en/shopware-6-en/extensions/store-api
 */
class ShopwareStoreApiClient
{
    private const STORE_API_URL = 'https://api.shopware.com';
    private const PLUGIN_NAME = 'ShopmasterZalandoConnectorSix';

    // Booking-Tarife (In-App-Käufe)
    private const BOOKING_GROUP = 'Shopmaster_zd_saleschannel';
    private const BOOKING_TARIF_FR = 'Shopmaster_zd_saleschannel';
    private const BOOKING_TARIF_DACH = 'Shopmaster_zd_saleschanneldach';

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly Logger $logger
    ) {
        // Logger name is set via withName() in the service that creates this logger
    }

    /**
     * Get licensed sales channels from Shopware Store
     *
     * @param Context $context
     * @return array Array of licensed channel IDs (e.g., ['de', 'fr', 'at', 'ch'])
     * @throws \Exception
     */
    public function getLicensedChannels(Context $context): array
    {
        try {
            // DE is always included in base license
            $licensedChannels = ['de'];

            // Get shop domain and plugin authentication
            $shopDomain = $this->getShopDomain();
            $shopSecret = $this->getShopSecret();

            if (!$shopDomain || !$shopSecret) {
                $this->logger->warning('Shop domain or secret not configured, returning only DE channel');
                return $licensedChannels;
            }

            // Call Shopware Store API to check active bookings
            $bookings = $this->getActiveBookings($shopDomain, $shopSecret);

            // Map bookings to sales channels
            foreach ($bookings as $booking) {
                $tariffName = $booking['bookingTariff'] ?? $booking['tariff'] ?? null;

                if (!$tariffName) {
                    continue;
                }

                // Check if booking belongs to our group
                if (!str_starts_with($tariffName, self::BOOKING_GROUP)) {
                    continue;
                }

                // Map tariff to sales channels
                $channels = $this->mapTariffToChannels($tariffName);
                $licensedChannels = array_merge($licensedChannels, $channels);
            }

            // Remove duplicates
            $licensedChannels = array_unique($licensedChannels);

            $this->logger->info('Licensed channels retrieved from Store API', [
                'channels' => $licensedChannels
            ]);

            return $licensedChannels;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get licensed channels from Store API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get active bookings from Shopware Store API
     *
     * @param string $shopDomain
     * @param string $shopSecret
     * @return array
     * @throws \Exception
     */
    private function getActiveBookings(string $shopDomain, string $shopSecret): array
    {
        try {
            $client = new Client([
                'base_uri' => self::STORE_API_URL,
                'timeout' => 10,
                'verify' => true
            ]);

            // Shopware Store API endpoint for plugin licenses
            // Documentation: https://docs.shopware.com/en/shopware-6-en/extensions/store-api
            $response = $client->post('/pluginStore/pluginByName', [
                'json' => [
                    'pluginName' => self::PLUGIN_NAME,
                    'language' => 'en',
                    'shopwareVersion' => '6.6.0'
                ],
                'headers' => [
                    'X-Shopware-Shop-Domain' => $shopDomain,
                    'X-Shopware-Shop-Secret' => $shopSecret,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Extract active bookings
            $bookings = $data['plugin']['bookings'] ?? [];

            // Filter only active bookings
            $activeBookings = array_filter($bookings, function ($booking) {
                $isActive = $booking['active'] ?? false;
                $expirationDate = $booking['expirationDate'] ?? null;

                // Check if booking is active and not expired
                if (!$isActive) {
                    return false;
                }

                if ($expirationDate) {
                    $expiry = new \DateTime($expirationDate);
                    $now = new \DateTime();

                    if ($now > $expiry) {
                        return false; // Expired
                    }
                }

                return true;
            });

            $this->logger->info('Retrieved bookings from Store API', [
                'totalBookings' => count($bookings),
                'activeBookings' => count($activeBookings)
            ]);

            return array_values($activeBookings);

        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve bookings from Store API', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Map tariff name to sales channel IDs
     *
     * @param string $tariffName
     * @return array
     */
    private function mapTariffToChannels(string $tariffName): array
    {
        // Tarif 1: Frankreich (FR)
        if ($tariffName === self::BOOKING_TARIF_FR) {
            return ['fr'];
        }

        // Tarif 2: DACH (AT + CH gleichzeitig)
        if ($tariffName === self::BOOKING_TARIF_DACH) {
            return ['at', 'ch'];
        }

        $this->logger->warning('Unknown tariff name', [
            'tariffName' => $tariffName
        ]);

        return [];
    }

    /**
     * Get shop domain from configuration
     *
     * @return string|null
     */
    private function getShopDomain(): ?string
    {
        // Shopware provides shop domain in system config
        // Alternative: Use APP_URL from .env or shopware.store.frw.shop_id
        return $this->systemConfigService->get('core.store.shopSecret')
            ? parse_url($_SERVER['APP_URL'] ?? 'http://localhost', PHP_URL_HOST)
            : null;
    }

    /**
     * Get shop secret from configuration
     *
     * @return string|null
     */
    private function getShopSecret(): ?string
    {
        // Shop secret is stored in system config after store registration
        return $this->systemConfigService->get('core.store.shopSecret');
    }

    /**
     * Check if Store API is available and shop is registered
     *
     * @return bool
     */
    public function isStoreApiAvailable(): bool
    {
        $shopSecret = $this->getShopSecret();
        $shopDomain = $this->getShopDomain();

        return $shopSecret !== null && $shopDomain !== null;
    }
}
