<?php

declare(strict_types=1);

namespace AzureOss\Storage\Queue\Models;

use AzureOss\Storage\Queue\Exceptions\DeserializationException;

/**
 * Represents an Azure Storage queue message inspected without changing its visibility.
 *
 * Peeked messages do not include a pop receipt and therefore cannot be deleted or updated.
 */
final class PeekedMessage
{
    private function __construct(
        public readonly string $messageId,
        public readonly string $body,
        public readonly ?\DateTimeInterface $insertedOn,
        public readonly ?\DateTimeInterface $expiresOn,
        public readonly int $dequeueCount,
    ) {}

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        $insertedOn = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC1123, (string) $xml->InsertionTime);
        if ($insertedOn === false) {
            throw new DeserializationException('Azure returned a malformed date.');
        }

        $expiresOn = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC1123, (string) $xml->ExpirationTime);
        if ($expiresOn === false) {
            throw new DeserializationException('Azure returned a malformed date.');
        }

        return new self(
            (string) $xml->MessageId,
            (string) $xml->MessageText,
            $insertedOn,
            $expiresOn,
            (int) $xml->DequeueCount,
        );
    }
}
