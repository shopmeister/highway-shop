<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order\Address;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class ImportOrderAddressStruct extends Struct
{
    /**
     * @var string
     */
    protected string $id;
    /**
     * @var string
     */
    protected string $firstName;
    /**
     * @var string
     */
    protected string $lastName;
    /**
     * @var string
     */
    protected string $street;
    /**
     * @var string
     */
    protected string $zipcode;
    /**
     * @var string
     */
    protected string $countryId;
    /**
     * @var string
     */
    protected string $city;
    /**
     * @var string|null
     */
    protected ?string $phoneNumber = null;
    /**
     * @var string|null
     */
    protected ?string $additionalAddressLine1 = null;
    /**
     * @var string
     */
    protected string $salutationId;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     */
    public function setZipcode(string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return string
     */
    public function getCountryId(): string
    {
        return $this->countryId;
    }

    /**
     * @param string $countryId
     */
    public function setCountryId(string $countryId): void
    {
        $this->countryId = $countryId;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string|null
     */
    public function getAdditionalAddressLine1(): ?string
    {
        return $this->additionalAddressLine1;
    }

    /**
     * @param string|null $additionalAddressLine1
     */
    public function setAdditionalAddressLine1(?string $additionalAddressLine1): void
    {
        $this->additionalAddressLine1 = $additionalAddressLine1;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSalutationId(): string
    {
        return $this->salutationId;
    }

    /**
     * @param string $salutationId
     */
    public function setSalutationId(string $salutationId): void
    {
        $this->salutationId = $salutationId;
    }

}