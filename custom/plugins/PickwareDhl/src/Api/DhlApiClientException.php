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

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use JsonException;
use Pickware\ApiErrorHandlingBundle\JsonApiErrorTranslating\LocalizableJsonApiError;
use Pickware\PickwareDhl\Adapter\DhlAdapterException;
use Psr\Http\Message\ResponseInterface;

class DhlApiClientException extends DhlAdapterException
{
    public static function noBillingNumberConfiguredForProduct(DhlProduct $product): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'No billing number configured for product',
                'de' => 'Keine Abrechnungsnummer für Produkt konfiguriert',
            ],
            'detail' => [
                'en' => sprintf(
                    'No billing number configured for product %s.',
                    $product->getName(),
                ),
                'de' => sprintf(
                    'Für das Produkt %s ist keine Abrechnungsnummer konfiguriert.',
                    $product->getName(),
                ),
            ],
            'meta' => [
                'productName' => $product->getName(),
            ],
        ]));
    }

    public static function emailAddressMissingForParcelOutletRouting(): self
    {
        return new self(
            new LocalizableJsonApiError([
                'title' => [
                    'en' => 'Email address required for Retail outlet routing',
                    'de' => 'E-Mail-Adresse für Filial-Routing erforderlich',
                ],
                'detail' => [
                    'en' => 'The email address for the Retail outlet routing is missing. Please provide an email address.',
                    'de' => 'Die E-Mail-Adresse für das Filial-Routing fehlt. Bitte geben Sie eine E-Mail-Adresse an.',
                ],
            ]),
        );
    }

    public static function fromClientException(ClientException $e): self
    {
        $errorMessage = self::getErrorMessageFromJsonResponse($e->getResponse());

        return new self(
            new LocalizableJsonApiError([
                'title' => [
                    'en' => 'DHL Parcel API responded with an error',
                    'de' => 'DHL Parcel API hat einen Fehler zurückgegeben',
                ],
                'detail' => [
                    'en' => sprintf(
                        'The DHL Parcel API responded with an error: %s',
                        $errorMessage,
                    ),
                    'de' => sprintf(
                        'Die DHL Parcel API hat einen Fehler zurückgegeben: %s',
                        $errorMessage,
                    ),
                ],
                'meta' => [
                    'error' => $errorMessage,
                ],
            ]),
            $e,
        );
    }

    public static function fromServerException(ServerException $e): self
    {
        $errorMessage = self::getErrorMessageFromJsonResponse($e->getResponse());

        return new self(
            new LocalizableJsonApiError([
                'title' => [
                    'en' => 'DHL Parcel API responded with an error',
                    'de' => 'DHL Parcel API hat einen Fehler zurückgegeben',
                ],
                'detail' => [
                    'en' => sprintf(
                        'The DHL Parcel API request failed due to an unexpected DHL Server error: %s',
                        $errorMessage,
                    ),
                    'de' => sprintf(
                        'Die Anfrage an die DHL Parcel API ist aufgrund eines unerwarteten Fehlers auf dem DHL Server fehlgeschlagen: %s',
                        $errorMessage,
                    ),
                ],
                'meta' => [
                    'error' => $errorMessage,
                ],
            ]),
            $e,
        );
    }

    private static function getErrorMessageFromJsonResponse(ResponseInterface $responseInterface): string
    {
        try {
            $data = json_decode((string)$responseInterface->getBody(), flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $data = null;
        }

        $errorMessage = '';
        if ($data !== null) {
            if (property_exists($data, 'detail') && $data->detail !== '') {
                $errorMessage = $data->detail;
            }

            if (property_exists($data, 'items')) {
                foreach ($data->items as $item) {
                    foreach ($item->validationMessages as $validationMessage) {
                        $errorMessage .= sprintf('%s ', $validationMessage->validationMessage);
                    }
                }
            }

            if ($errorMessage === '') {
                $errorMessage = 'Error message parsing failed. Please contact support.';
            }
        } else {
            $errorMessage = 'The API returned an unsupported response format. Please try again later.';
        }

        return trim($errorMessage);
    }
}
