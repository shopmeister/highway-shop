<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order\Customer;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class ImportOrderCustomerStruct extends Struct
{
    protected string $email;
    protected string $salutationId;
    protected string $firstName;
    protected string $lastName;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
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

}