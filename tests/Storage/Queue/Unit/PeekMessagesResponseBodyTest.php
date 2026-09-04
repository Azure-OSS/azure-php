<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Queue\Unit;

use AzureOss\Storage\Queue\Responses\PeekMessagesResponseBody;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PeekMessagesResponseBodyTest extends TestCase
{
    #[Test]
    public function it_deserializes_messages_from_a_response(): void
    {
        $messages = PeekMessagesResponseBody::fromResponse(new Response(200, body: <<<'XML'
            <QueueMessagesList>
                <QueueMessage>
                    <MessageId>message-id</MessageId>
                    <InsertionTime>Sun, 27 Sep 2009 18:41:57 GMT</InsertionTime>
                    <ExpirationTime>Sun, 04 Oct 2009 18:41:57 GMT</ExpirationTime>
                    <DequeueCount>3</DequeueCount>
                    <MessageText>hello world</MessageText>
                </QueueMessage>
            </QueueMessagesList>
            XML));

        self::assertCount(1, $messages);
        self::assertSame('message-id', $messages[0]->messageId);
        self::assertSame('hello world', $messages[0]->body);
    }

    #[Test]
    public function it_deserializes_an_empty_message_list(): void
    {
        $body = PeekMessagesResponseBody::fromXml(new \SimpleXMLElement('<QueueMessagesList />'));

        self::assertSame([], $body->messages);
    }
}
