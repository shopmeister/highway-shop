<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\SignUpToken;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                          add(SignUpTokenEntity $entity)
 * @method void                          set(string $key, SignUpTokenEntity $entity)
 * @method \Generator<SignUpTokenEntity> getIterator()
 * @method SignUpTokenEntity[]           getElements()
 * @method SignUpTokenEntity|null        get(string $key)
 * @method SignUpTokenEntity|null        first()
 * @method SignUpTokenEntity|null        last()
 */
class SignUpTokenCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SignUpTokenEntity::class;
    }
}
