<?php declare(strict_types=1);

namespace NetzpShopmanager6\Core\Content\Statistic;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class StatisticEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $salesChannelId = null;
    protected ?string $hash = null;
    protected ?int $impressions = null;

    public function getSalesChannelId(): ?string { return $this->salesChannelId; }
    public function setSalesChannelId(string $value): void { $this->salesChannelId = $value; }

    public function getHash(): ?string { return $this->hash; }
    public function setHash(string $value): void { $this->hash = $value; }

    public function getImpressions(): ?int { return $this->impressions; }
    public function setImpressions(int $value): void { $this->impressions = $value; }
}
