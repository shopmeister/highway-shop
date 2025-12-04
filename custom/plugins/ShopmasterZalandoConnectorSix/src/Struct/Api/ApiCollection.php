<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api;

use ShopmasterZalandoConnectorSix\Struct\Collection;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

class ApiCollection extends Collection
{
    private ?ResponseStruct $response = null;

    public function __construct($elements = [])
    {
        if (!$elements || !$this->getExpectedClass()) {
            return;
        }
        foreach ($elements as $element) {
            if ($element instanceof ApiStruct) {
                $this->add($element);
            } elseif (is_array($element)) {
                $structClass = $this->getExpectedClass();
                $struct = new $structClass($element);
                $this->add($struct);
            }
        }
    }

    /**
     * @return ResponseStruct|null
     */
    public function getResponse(): ?ResponseStruct
    {
        return $this->response;
    }

    /**
     * @param ResponseStruct|null $response
     */
    public function setResponse(?ResponseStruct $response): void
    {
        $this->response = $response;
    }


}