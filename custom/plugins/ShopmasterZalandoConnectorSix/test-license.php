<?php

// Test script for License System
// Run inside Docker container: php custom/plugins/ShopmasterZalandoConnectorSix/test-license.php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Shopware\Core\Framework\Context;
use Shopware\Core\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Bootstrap Shopware
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? '1';

(new Dotenv())->bootEnv(dirname(__DIR__, 3) . '/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

// Get License Service
$licenseService = $container->get('ShopmasterZalandoConnectorSix\Services\License\LicenseService');
$salesChannelGuard = $container->get('ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard');

echo "\n========================================\n";
echo "License System Test\n";
echo "========================================\n\n";

// Test channels
$channels = ['de', 'fr', 'at', 'ch', 'uk', 'nl'];

echo "Testing individual channels:\n";
echo str_repeat("-", 60) . "\n";
printf("%-10s %-15s %-15s %-10s\n", "Channel", "Licensed", "Grace Period", "Allowed");
echo str_repeat("-", 60) . "\n";

foreach ($channels as $channel) {
    $isLicensed = $licenseService->isChannelLicensed($channel);
    $isGracePeriod = $licenseService->isInGracePeriod($channel);
    $status = $salesChannelGuard->getChannelStatus($channel);

    printf(
        "%-10s %-15s %-15s %-10s\n",
        strtoupper($channel),
        $status['licensed'] ? 'Yes' : 'No',
        $status['gracePeriod'] ? 'Yes (' . $status['daysLeft'] . 'd)' : 'No',
        $status['allowed'] ? 'Yes' : 'No'
    );
}

echo str_repeat("-", 60) . "\n\n";

// Get all licensed channels
$licensedChannels = $licenseService->getLicensedChannels();
echo "All licensed channels: " . implode(', ', array_map('strtoupper', $licensedChannels)) . "\n\n";

// Test Store API availability
$storeApiClient = $container->get('ShopmasterZalandoConnectorSix\Services\License\ShopwareStoreApiClient');
$isStoreApiAvailable = $storeApiClient->isStoreApiAvailable();

echo "Store API Status:\n";
echo "  Available: " . ($isStoreApiAvailable ? 'Yes' : 'No') . "\n";

if (!$isStoreApiAvailable) {
    echo "  Reason: Shop not registered or no shop secret configured\n";
    echo "  Fallback: Only DE channel licensed\n";
}

echo "\n========================================\n";
echo "License System Test Completed\n";
echo "========================================\n\n";
