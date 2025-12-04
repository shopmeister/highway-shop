<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api\Handler;

use Closure;
use GuzzleHttp\Psr7\Request;
use Pickware\DalBundle\EntityManager;
use Pickware\ShopwareExtensionsBundle\Context\ContextExtension;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\User\UserDefinition;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class LocaleHeaderMiddleware
{
    private string $localeCode;

    public function __construct(
        EntityManager $entityManager,
        Context $context,
    ) {
        if (ContextExtension::hasUser($context)) {
            $user = $entityManager->getByPrimaryKey(
                UserDefinition::class,
                ContextExtension::getUserId($context),
                $context,
                ['locale'],
            );

            $this->localeCode = $user->getLocale()?->getCode() ?? 'en-GB';
        } else {
            $this->localeCode = 'en-GB';
        }
    }

    public function __invoke(callable $handler): Closure
    {
        return function(Request $request, array $options) use ($handler) {
            $request = $request->withAddedHeader(header: 'Accept-Language', value: $this->localeCode);

            return $handler($request, $options);
        };
    }
}
