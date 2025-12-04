<?php

namespace ShopmasterZalandoConnectorSix\Services\Import\Order\Address;

use Doctrine\DBAL\Connection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderAddressStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Address\ImportOrderAddressStruct;
use ShopmasterZalandoConnectorSix\Struct\Struct;

class ImportOrderAddressService
{

    private Connection $connection;

    public function __construct(
        Connection $connection
    )
    {
        $this->connection = $connection;
    }

    public function convertAddressStruct(OrderAddressStruct $address): ImportOrderAddressStruct
    {
        $struct = new ImportOrderAddressStruct();
        $struct->setId(Struct::uuidToId($address->getAddressId()));
        $struct->setFirstName($address->getFirstName());
        $struct->setLastName($address->getLastName());
        $struct->setStreet($address->getAddressLine1());
        $struct->setAdditionalAddressLine1($address->getAddressLine2());
        $struct->setZipcode($address->getZipCode());
        $struct->setCity($address->getCity());
        $struct->setCountryId($this->getCountryIdByCode($address->getCountryCode()));
        $struct->setSalutationId($this->getDefaultSalutation());
        return $struct;
    }

    private function getCountryIdByCode(string $countryCode): string
    {
        $result = $this->connection->fetchOne('
            SELECT LOWER(HEX(`id`))
            FROM `country`
            WHERE `iso` = :iso;
        ', ['iso' => $countryCode]);


        return $result;
    }

    private function getDefaultSalutation(): string
    {
        $result = $this->connection->fetchOne('
            SELECT LOWER(HEX(`id`))
            FROM `salutation`
            WHERE `salutation_key` = :salutation_key;
        ', ['salutation_key' => 'not_specified']);


        return $result;
    }
}