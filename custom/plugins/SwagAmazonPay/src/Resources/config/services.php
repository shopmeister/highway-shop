<?php declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/** @var ContainerBuilder $container */
return static function () use ($container): void {
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/services'));

    $loader->load('controllers.xml');
    $loader->load('dal.xml');
    $loader->load('event_listeners.xml');
    $loader->load('hydrators.xml');
    $loader->load('services.xml');
    $loader->load('validation.xml');
};
