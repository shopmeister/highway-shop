<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\Button\Pay\AddressRestriction;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Country\CountryEntity;

readonly class AddressRestrictionService implements AddressRestrictionServiceInterface
{

    public function __construct(private EntityRepository $countryRepository)
    {

    }

    /**
     * @return array<string, array>
     */
    public function getAddressRestrictions(string $salesChannelId, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('shippingAvailable', true),
            new EqualsFilter('salesChannels.id', $salesChannelId)
        );

        $countries = $this->countryRepository->search($criteria, $context);

        $restrictions = [];

        /** @var CountryEntity $country */
        foreach ($countries as $country) {
            $countryIso = $country->getIso();
            if ($countryIso === null) {
                continue;
            }

            $restrictions[$countryIso] = [];
        }

        return $restrictions;
    }
}
