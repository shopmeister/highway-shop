<?php

namespace ShopmasterZalandoConnectorSix\Services\License;

use Monolog\Logger;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use ShopmasterZalandoConnectorSix\Struct\License\LicenseDataStruct;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LicenseService
{
    private const CACHE_KEY = 'shopmaster_zalando_license_data';
    private const CACHE_TTL = 3600; // 1 hour

    private const CONFIG_KEY_CHANNELS = 'ShopmasterZalandoConnectorSix.license.channels';
    private const CONFIG_KEY_LAST_VALIDATED = 'ShopmasterZalandoConnectorSix.license.lastValidated';
    private const CONFIG_KEY_GRACE_EXPIRY = 'ShopmasterZalandoConnectorSix.license.graceExpiry';

    private const GRACE_PERIOD_DAYS = 7;

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly CacheInterface $cache,
        private readonly Logger $logger,
        private readonly ShopwareStoreApiClient $storeApiClient
    ) {
        // Logger name is set via withName() in the service that creates this logger
    }

    /**
     * Check if a sales channel is licensed
     */
    public function isChannelLicensed(string $channelId): bool
    {
        // DE channel is always included (hardcoded)
        if ($channelId === 'de') {
            return true;
        }

        try {
            $licenseData = $this->getLicenseData();

            // Check if channel is licensed
            if ($licenseData->isChannelLicensed($channelId)) {
                return true;
            }

            // Check grace period
            if ($this->isInGracePeriod($channelId, $licenseData)) {
                return true;
            }

            return false;

        } catch (\Exception $e) {
            // FAILOVER: Bei API-Fehler ALLE Kanäle erlauben (sehr tolerant)
            $this->logger->warning('License validation failed, allowing all channels', [
                'channelId' => $channelId,
                'error' => $e->getMessage()
            ]);
            return true;
        }
    }

    /**
     * Get all licensed channels
     */
    public function getLicensedChannels(): array
    {
        try {
            $licenseData = $this->getLicenseData();
            $channels = $licenseData->getLicensedChannels();

            // DE is always included
            if (!in_array('de', $channels, true)) {
                $channels[] = 'de';
            }

            return $channels;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get licensed channels', [
                'error' => $e->getMessage()
            ]);
            // Failover: Return only DE
            return ['de'];
        }
    }

    /**
     * Check if channel is in grace period
     */
    public function isInGracePeriod(string $channelId, ?LicenseDataStruct $licenseData = null): bool
    {
        if ($channelId === 'de') {
            return false; // DE has no grace period (always licensed)
        }

        if ($licenseData === null) {
            $licenseData = $this->getLicenseData();
        }

        $expiry = $licenseData->getGraceExpiryForChannel($channelId);

        if ($expiry === null) {
            return false;
        }

        $now = new \DateTime();
        return $now < $expiry;
    }

    /**
     * Get grace period expiry date for a channel
     */
    public function getGraceExpiry(string $channelId): ?\DateTimeInterface
    {
        try {
            $licenseData = $this->getLicenseData();
            return $licenseData->getGraceExpiryForChannel($channelId);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get grace expiry', [
                'channelId' => $channelId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Set grace period expiry for a channel
     */
    public function setGraceExpiry(string $channelId, \DateTimeInterface $expiry): void
    {
        try {
            $graceExpiry = $this->systemConfigService->get(self::CONFIG_KEY_GRACE_EXPIRY) ?? [];

            if (!is_array($graceExpiry)) {
                $graceExpiry = [];
            }

            $graceExpiry[$channelId] = $expiry->format('Y-m-d H:i:s');

            $this->systemConfigService->set(self::CONFIG_KEY_GRACE_EXPIRY, $graceExpiry);

            // Invalidate cache
            $this->cache->delete(self::CACHE_KEY);

            $this->logger->info('Grace period set', [
                'channelId' => $channelId,
                'expiry' => $expiry->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to set grace expiry', [
                'channelId' => $channelId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Refresh license data from Store API
     */
    public function refreshLicenseData(Context $context): LicenseDataStruct
    {
        try {
            // Fetch licensed channels from Shopware Store API
            $licensedChannels = $this->fetchFromStoreApi($context);

            // Save to SystemConfig
            $this->systemConfigService->set(self::CONFIG_KEY_CHANNELS, $licensedChannels);
            $this->systemConfigService->set(self::CONFIG_KEY_LAST_VALIDATED, (new \DateTime())->format('Y-m-d H:i:s'));

            // Invalidate cache
            $this->cache->delete(self::CACHE_KEY);

            $this->logger->info('License data refreshed from Store API', [
                'licensedChannels' => $licensedChannels
            ]);

            return $this->buildLicenseData($licensedChannels);

        } catch (\Exception $e) {
            $this->logger->error('Failed to refresh license data', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get license data (from cache or SystemConfig)
     */
    private function getLicenseData(): LicenseDataStruct
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);

            // Load from SystemConfig
            $licensedChannels = $this->systemConfigService->get(self::CONFIG_KEY_CHANNELS) ?? [];
            return $this->buildLicenseData($licensedChannels);
        });
    }

    /**
     * Build LicenseDataStruct from channels array
     */
    private function buildLicenseData(array $licensedChannels): LicenseDataStruct
    {
        $licenseData = new LicenseDataStruct();
        $licenseData->setLicensedChannels($licensedChannels);

        // Load last validated timestamp
        $lastValidated = $this->systemConfigService->get(self::CONFIG_KEY_LAST_VALIDATED);
        if ($lastValidated) {
            $licenseData->setLastValidated(new \DateTime($lastValidated));
        }

        // Load grace expiry data
        $graceExpiry = $this->systemConfigService->get(self::CONFIG_KEY_GRACE_EXPIRY) ?? [];
        if (is_array($graceExpiry)) {
            $licenseData->setGraceExpiry($graceExpiry);
        }

        return $licenseData;
    }

    /**
     * Fetch license data from Shopware Store API
     */
    private function fetchFromStoreApi(Context $context): array
    {
        try {
            // Check if Store API is available
            if (!$this->storeApiClient->isStoreApiAvailable()) {
                $this->logger->warning('Store API not available (shop not registered), using fallback');
                return $this->getFallbackLicenses();
            }

            // Get licensed channels from Store API (includes DE + active bookings)
            $licensedChannels = $this->storeApiClient->getLicensedChannels($context);

            $this->logger->info('Successfully fetched licenses from Store API', [
                'licensedChannels' => $licensedChannels
            ]);

            return $licensedChannels;

        } catch (\Exception $e) {
            $this->logger->error('Store API call failed, using fallback', [
                'error' => $e->getMessage()
            ]);

            // Fallback: Return only DE channel
            return $this->getFallbackLicenses();
        }
    }

    /**
     * Get fallback licenses when Store API is unavailable
     *
     * @return array
     */
    private function getFallbackLicenses(): array
    {
        // Fallback: Only DE channel (always included in base license)
        return ['de'];
    }

    /**
     * Calculate days left in grace period
     */
    public function getDaysLeftInGracePeriod(string $channelId): int
    {
        $expiry = $this->getGraceExpiry($channelId);

        if ($expiry === null) {
            return 0;
        }

        $now = new \DateTime();
        $interval = $now->diff($expiry);

        if ($interval->invert === 1) {
            return 0; // Expired
        }

        return $interval->days;
    }
}
