<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */
 
declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Shopware\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigEntity;

/**
 * Fix for @see https://github.com/shopware/platform/issues/2672
 */
class CompleteDocumentConfigurationFactory
{
    private function __construct()
    {
        //Factory is Static
    }

    public static function createConfiguration(array $specificConfig, ?DocumentBaseConfigEntity ...$configs): CompleteDocumentConfiguration
    {
        $configs = array_filter($configs);
        $documentConfiguration = new CompleteDocumentConfiguration();
        foreach ($configs as $config) {
            $documentConfiguration = static::mergeConfiguration($documentConfiguration, $config);
        }
        $documentConfiguration = static::mergeConfiguration($documentConfiguration, $specificConfig);

        return $documentConfiguration;
    }

    /**
     * @param DocumentBaseConfigEntity|CompleteDocumentConfiguration|array $additionalConfig
     */
    public static function mergeConfiguration(CompleteDocumentConfiguration $baseConfig, $additionalConfig): CompleteDocumentConfiguration
    {
        $additionalConfigArray = [];
        if (\is_array($additionalConfig)) {
            $additionalConfigArray = $additionalConfig;
        } elseif (\is_object($additionalConfig)) {
            $additionalConfigArray = $additionalConfig->jsonSerialize();
        }

        $additionalConfigArray = self::cleanConfig($additionalConfigArray);

        foreach ($additionalConfigArray as $key => $value) {
            if ($value !== null) {
                if ($key === 'custom' && \is_array($value)) {
                    $baseConfig->__set('custom', array_merge((array) $baseConfig->__get('custom'), $value));
                } elseif (strncmp($key, 'custom.', 7) === 0) {
                    $customKey = mb_substr($key, 7);
                    $baseConfig->__set('custom', array_merge((array) $baseConfig->__get('custom'), [$customKey => $value]));
                } else {
                    $baseConfig->__set($key, $value);
                }
            }
        }

        return $baseConfig;
    }

    private static function cleanConfig(array $config): array
    {
        if (isset($config['config'])) {
            $config = array_merge($config, $config['config']);
            unset($config['config']);
        }

        $deleteKeys = [
            'viewData' => 1,
            '_uniqueIdentifier' => 1,
            'createdAt' => 1,
        ];

        return array_diff_key($config, $deleteKeys);
    }
}
