<?php
declare(strict_types=1);

use Shopware\Core\Framework\Adapter\Kernel\KernelFactory;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

$_SERVER['DATABASE_URL'] = "mysql://c1w7db1:XNf1zbu35cg@localhost:3306/c1w7db1";
$_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'];

$classLoader = require __DIR__ . '/../vendor/autoload.php';

// === boot Shopware like bin/console ===
$basePath = dirname(__DIR__);
$env = $_SERVER['APP_ENV'] ?? 'prod';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? false);

$kernel = (new KernelFactory($basePath))->create($env, $debug, $classLoader, null);
$kernel->boot();

$container = $kernel->getContainer();
$context = Context::createDefaultContext();

/** @var EntityRepository $productRepo */
$productRepo = $container->get('product.repository');

$criteria = (new Criteria())->setLimit(100000);
$products = $productRepo->search($criteria, $context);

$fp = fopen(__DIR__ . '/export.csv', 'w');

// write header
fputcsv($fp, ['ean', 'stock', 'price', 'list_price'], ';');

foreach ($products as $product) {
    $ean = $product->getEan();
    $stock = $product->getAvailableStock();

    $priceObj = $product->getPrice()?->first();
    $price = $priceObj?->getGross() ?? 0;
    $listPrice = $priceObj?->getListPrice()?->getGross() ?? 0;

    if ($ean) {
        fputcsv($fp, [$ean, $stock, $price, $listPrice], ';');
    }
}

fclose($fp);

echo "Export complete.\n";
