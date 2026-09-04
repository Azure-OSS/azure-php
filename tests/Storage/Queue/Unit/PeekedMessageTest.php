<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Queue\Unit;

use AzureOss\Storage\Queue\Models\PeekedMessage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PeekedMessageTest extends TestCase
{
    #[Test]
    public function it_deserializes_from_xml(): void
    {
        $message = PeekedMessage::fromXml(new \SimpleXMLElement(<<<'XML'
            <QueueMessage>
                <MessageId>message-id</MessageId>
                <InsertionTime>Sun, 27 Sep 2009 18:41:57 GMT</InsertionTime>
                <ExpirationTime>Sun, 04 Oct 2009 18:41:57 GMT</ExpirationTime>
                <DequeueCount>3</DequeueCount>
                <MessageText>PHNhbXBsZT5tZXNzYWdlPC9zYW1wbGU+</MessageText>
            </QueueMessage>
            XML));

        self::assertSame('message-id', $message->messageId);
        self::assertSame('PHNhbXBsZT5tZXNzYWdlPC9zYW1wbGU+', $message->body);
        self::assertSame('2009-09-27T18:41:57+00:00', $message->insertedOn?->format(\DateTimeInterface::ATOM));
        self::assertSame('2009-10-04T18:41:57+00:00', $message->expiresOn?->format(\DateTimeInterface::ATOM));
        self::assertSame(3, $message->dequeueCount);
    }
}
