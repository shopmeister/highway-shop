<?php

namespace ShopmasterZalandoConnectorSix\Core\Framework\MessageQueue;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessageInterface;
use Shopware\Core\Framework\MessageQueue\MessageQueueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StructCollectionMessageJsonSerializer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        // Prüfen, ob das Array das erwartete Format hat
        if (!is_array($data) || !isset($data[__CLASS__])) {
            throw new \InvalidArgumentException('Invalid data for denormalization. Expected array with key: ' . __CLASS__);
        }

        // Base64-Dekodierung
        $decoded = base64_decode($data[__CLASS__], true);
        if ($decoded === false) {
            throw MessageQueueException::cannotUnserializeMessage($data[__CLASS__]);
        }

        // Deserialisierung mit Sicherheitsmaßnahmen
        try {
            $deserialized = unserialize($decoded, ['allowed_classes' => true]);
        } catch (\Throwable $e) {
            throw new \Exception('Deserialization failed: ' . $e->getMessage());
        }

        return $deserialized;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $format === 'json' && isset($data[__CLASS__]);
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        // Sicherstellen, dass der Input das erwartete Interface implementiert
        if (!$object instanceof MessageInterface) {
            throw new \InvalidArgumentException('Invalid object for normalization. Must implement MessageInterface.');
        }

        // Serialisierung und Base64-Kodierung
        try {
            $serialized = serialize($object);
            $encoded = base64_encode($serialized);
        } catch (\Throwable $e) {
            throw new \Exception('Serialization failed: ' . $e->getMessage());
        }

        return [__CLASS__ => $encoded];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof MessageInterface && $format === 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        // Rückgabe des unterstützten Typs
        return [
            MessageInterface::class => true,
        ];
    }
}
