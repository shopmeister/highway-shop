<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api;

use ShopmasterZalandoConnectorSix\Struct\Struct;


abstract class ApiSetStruct extends Struct
{
    private ?string $scope = null;

    public function __toString(): string
    {
        $data = parent::__toString();
        if ($this->getScope()) {
            return '{"' . $this->getScope() . '": ' . $data . '}';
        }
        return $data;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string|null $scope
     * @return self
     */
    public function setScope(?string $scope): self
    {
        $this->scope = $scope;
        return $this;
    }
}