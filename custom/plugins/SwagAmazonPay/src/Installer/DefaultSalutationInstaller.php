<?php declare(strict_types=1);

namespace Swag\AmazonPay\Installer;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

readonly class DefaultSalutationInstaller
{
    /**
     * Since Amazon Pay does not provide any salutation information it's required to
     * choose a default salutation.
     */
    public const DEFAULT_SALUTATION_KEY = 'not_specified';


    public function __construct(private EntityRepository $salutationRepository)
    {
    }

    public function install(InstallContext $context): void
    {
        if ($this->defaultSalutationExists($context->getContext())) {
            return;
        }

        $defaultSalutationData = [
            'id' => Uuid::randomHex(),
            'salutationKey' => self::DEFAULT_SALUTATION_KEY,
            'displayName' => 'Not specified',
            'letterName' => 'Hello',
            'translations' => [
                'de-DE' => [
                    'displayName' => 'Keine Angabe',
                    'letterName' => 'Guten Tag',
                ],
                'en-GB' => [
                    'displayName' => 'Not specified',
                    'letterName' => 'Hello',
                ],
            ],
        ];

        $this->salutationRepository->upsert([$defaultSalutationData], $context->getContext());
    }

    private function defaultSalutationExists(Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('salutationKey', DefaultSalutationInstaller::DEFAULT_SALUTATION_KEY)
        );

        $salutationIds = $this->salutationRepository->searchIds($criteria, $context);

        if ($salutationIds->getTotal() === 0) {
            return false;
        }

        return true;
    }
}
