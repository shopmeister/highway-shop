<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api;

use InvalidArgumentException;
use JsonSerializable;

class DhlProduct implements JsonSerializable
{
    public const CODE_DHL_PAKET = 'V01PAK';
    public const CODE_DHL_PAKET_INTERNATIONAL = 'V53WPAK';
    public const CODE_DHL_EUROPAKET = 'V54EPAK';
    public const CODE_DHL_KLEINPAKET = 'V62KP';
    public const CODE_DHL_WARENPOST_INTERNATIONAL = 'V66WPI';
    public const CODE_DHL_RETURNS = 'dhlRetoure';
    public const CODES = [
        self::CODE_DHL_PAKET,
        self::CODE_DHL_PAKET_INTERNATIONAL,
        self::CODE_DHL_EUROPAKET,
        self::CODE_DHL_KLEINPAKET,
        self::CODE_DHL_WARENPOST_INTERNATIONAL,
        self::CODE_DHL_RETURNS,
    ];
    private const PRODUCT_CODE_NAME_MAPPING = [
        self::CODE_DHL_PAKET => 'DHL Paket',
        self::CODE_DHL_PAKET_INTERNATIONAL => 'DHL Paket International',
        self::CODE_DHL_EUROPAKET => 'DHL Europaket',
        self::CODE_DHL_KLEINPAKET => 'DHL Kleinpaket',
        self::CODE_DHL_WARENPOST_INTERNATIONAL => 'DHL Warenpost International',
        self::CODE_DHL_RETURNS => 'DHL Retoure',
    ];

    public static function getByCode(string $code): self
    {
        if (!self::isValidProductCode($code)) {
            throw new InvalidArgumentException(sprintf('DHL product with code %s does not exist', $code));
        }

        return new self($code);
    }

    public static function isValidProductCode(string $code): bool
    {
        return array_key_exists($code, self::PRODUCT_CODE_NAME_MAPPING);
    }

    public static function getReturnProduct(): self
    {
        return new self(self::CODE_DHL_RETURNS);
    }

    /**
     * @return self[]
     */
    public static function getList(): array
    {
        return array_map(fn(string $code) => self::getByCode($code), array_keys(self::PRODUCT_CODE_NAME_MAPPING));
    }

    private function __construct(private readonly string $code) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getProcedure(): string
    {
        if ($this->code === self::CODE_DHL_RETURNS) {
            // The "DHL Beilegretoure für DHL Paket" and "DHL Beilegretoure für DHL Warenpost" have a fixed procedure of "07"
            return '07';
        }

        return self::extractProcedureFromCode($this->code);
    }

    private static function extractProcedureFromCode(string $code): string
    {
        return mb_substr($code, 1, 2);
    }

    public function getName(): string
    {
        return self::PRODUCT_CODE_NAME_MAPPING[$this->code];
    }

    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->getName(),
            'procedure' => $this->getProcedure(),
        ];
    }
}
