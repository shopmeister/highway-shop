<?php

namespace ShopmasterZalandoConnectorSix\Services\License;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\Exception\License\SalesChannelNotLicensedException;

class SalesChannelGuard
{
    public function __construct(
        private readonly LicenseService $licenseService,
        private readonly Logger $logger
    ) {
        // Logger name is set via withName() in the service that creates this logger
    }

    /**
     * Guard a sales channel - throws exception if not licensed
     *
     * @param string $channelId
     * @throws SalesChannelNotLicensedException
     */
    public function guardSalesChannel(string $channelId): void
    {
        // Check if channel is licensed
        if ($this->licenseService->isChannelLicensed($channelId)) {
            // Check if in grace period (for logging purposes)
            if ($this->licenseService->isInGracePeriod($channelId)) {
                $daysLeft = $this->licenseService->getDaysLeftInGracePeriod($channelId);
                $this->logger->warning('Sales channel in grace period', [
                    'channelId' => $channelId,
                    'daysLeft' => $daysLeft
                ]);
            }
            return; // OK
        }

        // Not licensed and not in grace period
        $this->logger->error('Sales channel not licensed', [
            'channelId' => $channelId
        ]);

        throw new SalesChannelNotLicensedException($channelId);
    }

    /**
     * Check if sales channel is allowed (without throwing exception)
     *
     * @param string $channelId
     * @return bool
     */
    public function isChannelAllowed(string $channelId): bool
    {
        try {
            $this->guardSalesChannel($channelId);
            return true;
        } catch (SalesChannelNotLicensedException $e) {
            return false;
        }
    }

    /**
     * Get status information for a sales channel
     *
     * @param string $channelId
     * @return array{licensed: bool, gracePeriod: bool, daysLeft: int, allowed: bool}
     */
    public function getChannelStatus(string $channelId): array
    {
        $licensed = $this->licenseService->isChannelLicensed($channelId);
        $gracePeriod = $this->licenseService->isInGracePeriod($channelId);
        $daysLeft = $gracePeriod ? $this->licenseService->getDaysLeftInGracePeriod($channelId) : 0;

        // Channel is allowed if licensed OR in grace period
        // But we distinguish between licensed and grace period for UI display
        $actuallyLicensed = $licensed && !$gracePeriod;

        return [
            'licensed' => $actuallyLicensed,
            'gracePeriod' => $gracePeriod,
            'daysLeft' => $daysLeft,
            'allowed' => $licensed || $gracePeriod
        ];
    }
}
