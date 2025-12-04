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
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlBankTransferData
{
    public const CUSTOMER_REFERENCE_TEMPLATE_PLACEHOLDER_NAME = '{{ customerReference }}';

    private string $iban;
    private string $accountOwnerName;
    private string $bankName;
    private string $note1;
    private string $note2;
    private string $bic;
    private string $accountReference;

    public function __construct(array $config)
    {
        $this->iban = (string) ($config['iban'] ?? '');
        $this->accountOwnerName = (string) ($config['accountOwnerName'] ?? '');
        $this->bankName = (string) ($config['bankName'] ?? '');
        $this->note1 = (string) ($config['note1'] ?? '');
        $this->note2 = (string) ($config['note2'] ?? '');
        $this->bic = (string) ($config['bic'] ?? '');
        $this->accountReference = (string) ($config['accountReference'] ?? '');

        $requiredFields = [
            'iban',
            'accountOwnerName',
            'bankName',
        ];
        foreach ($requiredFields as $requiredField) {
            if ($this->$requiredField === '') {
                throw new InvalidArgumentException(sprintf(
                    'Property %s of class %s must be a non empty string.',
                    $requiredField,
                    self::class,
                ));
            }
        }
    }

    public function getAsArrayForShipmentDetails(string $customerReference): array
    {
        return [
            'bankAccount' => [
                'accountHolder' => $this->accountOwnerName,
                'bankName' => $this->bankName,
                'iban' => $this->iban,
                'bic' => $this->bic,
            ],
            'transferNote1' => self::insertCustomerReference($this->note1, $customerReference),
            'transferNote2' => self::insertCustomerReference($this->note2, $customerReference),
            'accountReference' => self::insertCustomerReference($this->accountReference, $customerReference),
        ];
    }

    private static function insertCustomerReference(string $string, string $customerReference): string
    {
        return str_replace(self::CUSTOMER_REFERENCE_TEMPLATE_PLACEHOLDER_NAME, $customerReference, $string);
    }
}
