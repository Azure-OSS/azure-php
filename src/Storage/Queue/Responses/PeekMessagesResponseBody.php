<?php

declare(strict_types=1);

namespace AzureOss\Storage\Queue\Responses;

use AzureOss\Storage\Queue\Models\PeekedMessage;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class PeekMessagesResponseBody
{
    /**
     * @param  PeekedMessage[]  $messages
     */
    private function __construct(
        public readonly array $messages,
    ) {}

    /**
     * @return PeekedMessage[]
     */
    public static function fromResponse(ResponseInterface $response): array
    {
        return self::fromXml(new \SimpleXMLElement($response->getBody()->getContents()))->messages;
    }

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        $messages = [];
        foreach ($xml->QueueMessage as $message) {
            $messages[] = PeekedMessage::fromXml($message);
        }

        return new self($messages);
    }
}
