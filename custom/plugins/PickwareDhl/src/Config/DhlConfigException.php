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

use Pickware\ApiErrorHandlingBundle\JsonApiErrorTranslating\LocalizableJsonApiError;
use Pickware\HttpUtils\JsonApi\JsonApiErrorLinks;
use Pickware\HttpUtils\JsonApi\JsonApiLinkObject;
use Pickware\ShippingBundle\Config\ConfigException;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlConfigException extends ConfigException
{
    public const ERROR_CODE_NAMESPACE = 'PICKWARE_DHL__SHIPPING__CONFIG__';
    public const ERROR_CODE_UNSUPPORTED_RETURN_RECEIVER = self::ERROR_CODE_NAMESPACE . 'UNSUPPORTED_RETURN_RECEIVER';

    public static function unsupportedReturnReceiver(string $returnReceiverId): self
    {
        return new self(new LocalizableJsonApiError([
            'code' => self::ERROR_CODE_UNSUPPORTED_RETURN_RECEIVER,
            'title' => [
                'de' => 'Retourenempfänger wird nicht unterstützt',
                'en' => 'Unsupported return receiver',
            ],
            'detail' => [
                'de' => sprintf(
                    'Das Erstellen von Retourenetiketten durch DHL Retoure International wird für das ausgewählte Land (%s) aktuell nicht unterstützt.',
                    mb_strtoupper($returnReceiverId),
                ),
                'en' => sprintf(
                    'Creating return labels for the selected country (%s) is currently not supported by DHL Retoure International.',
                    mb_strtoupper($returnReceiverId),
                ),
            ],
            'links' => [
                'de' => new JsonApiErrorLinks(
                    type: new JsonApiLinkObject(
                        href: 'https://www.dhl.de/de/geschaeftskunden/paket/leistungen-und-services/internationaler-versand/retoure-international.html',
                        title: 'Weitere Informationen zu DHL Retoure International',
                        hreflang: 'de-DE',
                    ),
                ),
                'en' => new JsonApiErrorLinks(
                    type: new JsonApiLinkObject(
                        href: 'https://www.dhl.de/en/geschaeftskunden/paket/leistungen-und-services/internationaler-versand/retoure-international.html',
                        title: 'More information about DHL Retoure International',
                        hreflang: 'en-GB',
                    ),
                ),
            ],
            'meta' => [
                'configDomain' => self::ERROR_CODE_NAMESPACE,
            ],
        ]));
    }
}
