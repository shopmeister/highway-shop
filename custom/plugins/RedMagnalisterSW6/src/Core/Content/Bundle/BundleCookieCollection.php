<?php declare(strict_types=1);
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

namespace Redgecko\Magnalister\Core\Content\Bundle;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(BundleCookieEntity $entity)
 * @method void              set(string $key, BundleCookieEntity $entity)
 * @method BundleCookieEntity[]    getIterator()
 * @method BundleCookieEntity[]    getElements()
 * @method BundleCookieEntity|null get(string $key)
 * @method BundleCookieEntity|null first()
 * @method BundleCookieEntity|null last()
 */
class BundleCookieCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BundleCookieEntity::class;
    }
}
