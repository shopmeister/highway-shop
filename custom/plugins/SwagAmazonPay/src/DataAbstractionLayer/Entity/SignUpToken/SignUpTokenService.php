<?php declare(strict_types=1);

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\SignUpToken;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\Uuid\Uuid;

readonly class SignUpTokenService implements SignUpTokenServiceInterface
{
    public function __construct(private EntityRepository $signUpTokenRepository)
    {
    }

    public function create(Context $context): string
    {
        $id = Uuid::randomHex();
        $this->signUpTokenRepository->create([
            [
                'id' => $id,
            ],
        ], $context);

        return $id;
    }

    public function validate(string $id, Context $context): bool
    {
        if (!Uuid::isValid($id)) {
            return false;
        }

        $criteria = new Criteria([$id]);
        $criteria->setLimit(1);

        $tokenValid = $this->signUpTokenRepository->searchIds($criteria, $context)->getTotal() === 1;
        if (!$tokenValid) {
            return false;
        }

        $this->signUpTokenRepository->delete([
            [
                'id' => $id,
            ],
        ], $context);

        return true;
    }

    public function cleanup(Context $context): void
    {
        $lastWeek = new \DateTime('now -7 days');
        $lastWeek = $lastWeek->setTimezone(new \DateTimeZone('UTC'));

        $criteria = new Criteria();
        $criteria->setLimit(500);
        $criteria->addFilter(
            new RangeFilter(
                'createdAt',
                [
                    RangeFilter::LTE => $lastWeek->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            )
        );

        $ids = $this->signUpTokenRepository->searchIds($criteria, $context)->getIds();

        $deleteData = [];
        foreach ($ids as $id) {
            $deleteData[] = [
                'id' => $id,
            ];
        }

        if ($deleteData === []) {
            return;
        }

        $this->signUpTokenRepository->delete($deleteData, $context);
    }
}
