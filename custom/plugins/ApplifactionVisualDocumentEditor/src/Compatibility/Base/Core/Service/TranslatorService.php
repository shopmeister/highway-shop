<?php declare(strict_types=1);
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageLoaderInterface;
use Shopware\Core\System\User\UserEntity;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorService
{

    const FALLBACK_LOCALE_CODE = 'en-GB';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityRepository    $userRepository,
        private readonly EntityRepository    $languageRepository
    )
    {
    }

    public function trans(string $key, Context $context, array $params = [], ?string $localeCode = null): string
    {
        return $this->translator->trans($key, $params, null, $localeCode ?? $this->getAdminUserLocaleCode($context));
    }

    public function getAdminUserLocaleCode(Context $context): ?string
    {
        if ($context->getSource() instanceof AdminApiSource && $userId = $context->getSource()->getUserId()) {

            $criteria = new Criteria([$userId]);
            $criteria->addAssociation('locale');
            /** @var UserEntity|null $user */
            $user = $this->userRepository->search($criteria, $context)->first();
            if ($user === null || $user->getLocale() === null) {
                return self::FALLBACK_LOCALE_CODE;
            }
            return $user->getLocale()->getCode();

        } else {

            $criteria = new Criteria([$context->getLanguageId()]);
            $criteria->addAssociation('locale');
            $language = $this->languageRepository->search($criteria, $context)->get($context->getLanguageId());
            if ($language && $language->getLocale()) {
                return $language->getLocale()->getCode();
            }

        }

        return self::FALLBACK_LOCALE_CODE; // No logged-in user

    }

    public function getAdminUserLocaleCodeUnderscoreFormat(Context $context): ?string
    {
        return str_replace('-', '_', $this->getAdminUserLocaleCode($context));
    }

}