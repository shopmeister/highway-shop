<?php

namespace ShopmasterZalandoConnectorSix\Struct\License;

use Shopware\Core\Framework\Struct\Struct;

class LicenseDataStruct extends Struct
{
    protected array $licensedChannels = [];
    protected ?\DateTimeInterface $lastValidated = null;
    protected array $graceExpiry = [];

    public function getLicensedChannels(): array
    {
        return $this->licensedChannels;
    }

    public function setLicensedChannels(array $licensedChannels): self
    {
        $this->licensedChannels = $licensedChannels;
        return $this;
    }

    public function isChannelLicensed(string $channelId): bool
    {
        return in_array($channelId, $this->licensedChannels, true);
    }

    public function addLicensedChannel(string $channelId): self
    {
        if (!$this->isChannelLicensed($channelId)) {
            $this->licensedChannels[] = $channelId;
        }
        return $this;
    }

    public function getLastValidated(): ?\DateTimeInterface
    {
        return $this->lastValidated;
    }

    public function setLastValidated(?\DateTimeInterface $lastValidated): self
    {
        $this->lastValidated = $lastValidated;
        return $this;
    }

    public function getGraceExpiry(): array
    {
        return $this->graceExpiry;
    }

    public function setGraceExpiry(array $graceExpiry): self
    {
        $this->graceExpiry = $graceExpiry;
        return $this;
    }

    public function getGraceExpiryForChannel(string $channelId): ?\DateTimeInterface
    {
        if (!isset($this->graceExpiry[$channelId])) {
            return null;
        }

        $expiry = $this->graceExpiry[$channelId];

        if ($expiry instanceof \DateTimeInterface) {
            return $expiry;
        }

        if (is_string($expiry)) {
            return new \DateTime($expiry);
        }

        return null;
    }

    public function setGraceExpiryForChannel(string $channelId, \DateTimeInterface $expiry): self
    {
        $this->graceExpiry[$channelId] = $expiry->format('Y-m-d H:i:s');
        return $this;
    }

    public function removeGraceExpiryForChannel(string $channelId): self
    {
        unset($this->graceExpiry[$channelId]);
        return $this;
    }
}
