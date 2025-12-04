<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl;

use Pickware\ApiErrorHandlingBundle\JsonApiErrorTranslating\LocalizableJsonApiError;
use Pickware\HttpUtils\JsonApi\JsonApiError;
use Pickware\HttpUtils\JsonApi\JsonApiErrorLinks;
use Pickware\HttpUtils\JsonApi\JsonApiLinkObject;
use Pickware\ShippingBundle\Carrier\CarrierAdapterException;
use Throwable;

class DhlException extends CarrierAdapterException
{
    public function __construct(JsonApiError $jsonApiError, ?Throwable $previous = null)
    {
        parent::__construct(self::addHelpcenterLinkToError($jsonApiError), $previous);
    }

    public static function addHelpcenterLinkToError(JsonApiError $jsonApiError): LocalizableJsonApiError
    {
        $localizableJsonApiError = LocalizableJsonApiError::createFromJsonApiError($jsonApiError);
        $localizableJsonApiError->setLocalizedLinks([
            'en' => new JsonApiErrorLinks(
                type: new JsonApiLinkObject(
                    href: 'https://help-sw6.pickware.com/hc/de/articles/10044371111324-Bekannte-Fehlermeldungen-bei-DHL',
                    title: 'Please also visit our helpcenter',
                    hreflang: 'de-DE',
                ),
            ),
            'de' => new JsonApiErrorLinks(
                type: new JsonApiLinkObject(
                    href: 'https://help-sw6.pickware.com/hc/de/articles/10044371111324-Bekannte-Fehlermeldungen-bei-DHL',
                    title: 'Weitere Infos findest du im Helpcenter',
                    hreflang: 'de-DE',
                ),
            ),
        ]);

        return $localizableJsonApiError;
    }
}
