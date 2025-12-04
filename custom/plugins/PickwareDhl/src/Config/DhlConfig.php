<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Config;

use Pickware\PickwareDhl\Api\DhlApiClientConfig;
use Pickware\PickwareDhl\Api\DhlBankTransferData;
use Pickware\PickwareDhl\Api\DhlBillingInformation;
use Pickware\PickwareDhl\Api\DhlParcelDeSubSystem;
use Pickware\PickwareDhl\Api\DhlProduct;
use Pickware\ShippingBundle\Config\ConfigDecoratorTrait;
use Pickware\ShippingBundle\Shipment\Country;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlConfig
{
    use ConfigDecoratorTrait;

    public const CONFIG_DOMAIN = 'PickwareDhl.dhl';
    public const SUPPORTED_RETURN_RECEIVER_COUNTRY_CODES = [
        'aut',
        'bel',
        'bgr',
        'che',
        'cyp',
        'cze',
        'deu',
        'dnk',
        'esp',
        'est',
        'fin',
        'fra',
        'gbr',
        'grc',
        'hrv',
        'hun',
        'irl',
        'ita',
        'ltu',
        'lux',
        'lva',
        'mlt',
        'nld',
        'pol',
        'prt',
        'rou',
        'svk',
        'svn',
        'swe',
    ];

    public function assertConfigurationIsComplete(): void
    {
        $this->config->assertNotEmpty('username');
        $this->config->assertNotEmpty('password');
        $this->assertValidCustomerNumber();
    }

    public function getDhlParcelApiClientConfig(DhlParcelDeSubSystem $subSystem): DhlApiClientConfig
    {
        $this->config->assertNotEmpty('username');
        $this->config->assertNotEmpty('password');

        $useTestingEndpoint = $this->config['useTestingEndpoint'] ?? false;

        return new DhlApiClientConfig(
            username: $useTestingEndpoint ? $subSystem->getTestUsername() : $this->config['username'],
            password: $useTestingEndpoint ? $subSystem->getTestPassword() : $this->config['password'],
            useTestingEndpoint: $useTestingEndpoint,
        );
    }

    public function getDhlApiClientConfig(): DhlApiClientConfig
    {
        $this->config->assertNotEmpty('username');
        $this->config->assertNotEmpty('password');

        return new DhlApiClientConfig(
            username: $this->config['username'],
            password: $this->config['password'],
            useTestingEndpoint: $this->config['useTestingEndpoint'] ?? false,
        );
    }

    public function getBankTransferData(): ?DhlBankTransferData
    {
        $bankTransferDataConfig = [];
        foreach ($this->config as $key => $value) {
            if (mb_strpos($key, 'bankTransferData') === 0) {
                $shortKey = lcfirst(str_replace('bankTransferData', '', $key));
                $bankTransferDataConfig[$shortKey] = $value;
            }
        }

        $this->config->assertNotEmpty('bankTransferDataIban');
        $this->config->assertNotEmpty('bankTransferDataBankName');
        $this->config->assertNotEmpty('bankTransferDataAccountOwnerName');

        return new DhlBankTransferData($bankTransferDataConfig);
    }

    public function getBillingInformation(): DhlBillingInformation
    {
        $billingInformation = new DhlBillingInformation();

        foreach (DhlProduct::getList() as $dhlProduct) {
            $billingNumberConfigKey = 'billingNumber' . $dhlProduct->getCode();
            if (isset($this->config[$billingNumberConfigKey]) && $this->config[$billingNumberConfigKey] !== '') {
                $billingInformation->setBillingNumberForProduct($dhlProduct, $this->config[$billingNumberConfigKey]);
            }
        }

        return $billingInformation;
    }

    private function assertValidCustomerNumber(): void
    {
        $this->config->assertNotEmpty('customerNumber');
        $this->config->assertMatchRegex('customerNumber', '/^\\d{10}$/');
    }

    public function getCustomerNumber(): string
    {
        $this->assertValidCustomerNumber();

        return $this->config['customerNumber'];
    }

    public function getReturnReceiverId(Country $country): string
    {
        if (!in_array($country->getIso3Code(), self::SUPPORTED_RETURN_RECEIVER_COUNTRY_CODES)) {
            throw DhlConfigException::unsupportedReturnReceiver($country->getIso3Code());
        }

        $key = 'returnReceiver' . ucfirst($country->getIso2Code());
        $this->config->assertNotEmpty($key);

        return $this->config[$key];
    }
}
