<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api;

use ShopmasterZalandoConnectorSix\Exception\Struct\StructException;
use ShopmasterZalandoConnectorSix\Struct\Struct;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

abstract class ApiStruct extends Struct
{

    /**
     * @param array|null $data
     * @param bool $skipException
     * @throws StructException
     */
    public function __construct(?array $data = null, bool $skipException = true)
    {
        unset($this->extensions);
        if (!$data) {
            return;
        }
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();

        foreach ($data as $key => $datum) {
            $methodName = $nameConverter->denormalize('set_' . $key);
            if (!method_exists($this, $methodName)) {
                if (!$skipException) {
                    throw new StructException('Can`t find method ' . $methodName . ' on class ' . self::class);
                }
            } else {
                try {
                    $this->$methodName($datum);
                } catch (\TypeError $exception) {
//                    throw $exception;
                }
//                catch (\Throwable $exception) {
//                    throw $exception;
//                }

            }
        }
    }
}