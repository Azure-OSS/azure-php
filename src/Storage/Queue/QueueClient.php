<?php

declare(strict_types=1);

namespace AzureOss\Storage\Queue;

use AzureOss\Identity\TokenCredential;
use AzureOss\Storage\Common\Auth\StorageSharedKeyCredential;
use AzureOss\Storage\Common\Helpers\HttpRequestHelper;
use AzureOss\Storage\Common\Middleware\ClientFactory;
use AzureOss\Storage\Queue\Exceptions\QueueStorageException;
use AzureOss\Storage\Queue\Exceptions\QueueStorageExceptionDeserializer;
use AzureOss\Storage\Queue\Models\PeekedMessage;
use AzureOss\Storage\Queue\Models\QueueClientOptions;
use AzureOss\Storage\Queue\Models\QueueErrorCode;
use AzureOss\Storage\Queue\Models\QueueMessage;
use AzureOss\Storage\Queue\Models\QueueProperties;
use AzureOss\Storage\Queue\Models\SendReceipt;
use AzureOss\Storage\Queue\Models\UpdateReceipt;
use AzureOss\Storage\Queue\Requests\QueueMessageRequestBody;
use AzureOss\Storage\Queue\Responses\PeekMessagesResponseBody;
use AzureOss\Storage\Queue\Responses\ReceiveMessagesResponseBody;
use AzureOss\Storage\Queue\Responses\SendMessageResponseBody;
use AzureOss\Storage\Queue\Responses\UpdateMessageResponseBody;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\UriInterface;

/**
 * Provides operations for an Azure Storage queue and its messages.
 *
 * Synchronous methods wait for their corresponding asynchronous operation. Async
 * methods return a Guzzle promise that resolves to the documented result type.
 */
final class QueueClient
{
    private readonly Client $client;

    public readonly string $queueName;

    /**
     * @param  UriInterface  $uri  URI of the queue, including any SAS query string.
     * @param  StorageSharedKeyCredential|TokenCredential|null  $credential  Credential used to authorize requests, or null for SAS access.
     * @param  QueueClientOptions  $options  Client transport and service-version options.
     */
    public function __construct(
        public UriInterface $uri,
        public readonly StorageSharedKeyCredential|TokenCredential|null $credential = null,
        private readonly QueueClientOptions $options = new QueueClientOptions,
    ) {
        // must always include the forward slash (/) to separate the host name from the path and query portions of the URI.
        $this->uri = $uri->withPath(rtrim($uri->getPath(), '/'));
        $this->queueName = basename($this->uri->getPath());
        $this->client = (new ClientFactory)->create($this->uri, $credential, new QueueStorageExceptionDeserializer, $this->options->httpClientOptions, $this->options->apiVersion);
    }

    /** Creates the queue. */
    public function create(): void
    {
        $this->createAsync()->wait();
    }

    /** Asynchronously creates the queue. */
    public function createAsync(): PromiseInterface
    {
        return $this->client->putAsync($this->uri);
    }

    /** Creates the queue if it does not already exist. */
    public function createIfNotExists(): void
    {
        $this->createIfNotExistsAsync()->wait();
    }

    /** Asynchronously creates the queue if it does not already exist. */
    public function createIfNotExistsAsync(): PromiseInterface
    {
        return $this->createAsync()
            ->otherwise(function (mixed $reason) {
                $e = Create::exceptionFor($reason);

                if ($e instanceof QueueStorageException && $e->errorCode === QueueErrorCode::QueueAlreadyExists) {
                    return;
                }

                throw $e;
            });
    }

    /** Deletes the queue. */
    public function delete(): void
    {
        $this->deleteAsync()->wait();
    }

    /** Asynchronously deletes the queue. */
    public function deleteAsync(): PromiseInterface
    {
        return $this->client->deleteAsync($this->uri);
    }

    /** Deletes the queue if it exists. */
    public function deleteIfExists(): void
    {
        $this->deleteIfExistsAsync()->wait();
    }

    /** Asynchronously deletes the queue if it exists. */
    public function deleteIfExistsAsync(): PromiseInterface
    {
        return $this->deleteAsync()
            ->otherwise(function (mixed $reason) {
                $e = Create::exceptionFor($reason);

                if ($e instanceof QueueStorageException && $e->errorCode === QueueErrorCode::QueueNotFound) {
                    return;
                }

                throw $e;
            });
    }

    /** Determines whether the queue exists. */
    public function exists(): bool
    {
        return $this->existsAsync()->wait();
    }

    /**
     * Asynchronously determines whether the queue exists.
     *
     * @return PromiseInterface<bool, \Throwable>
     */
    public function existsAsync(): PromiseInterface
    {
        return $this->client
            ->headAsync($this->uri, [
                RequestOptions::QUERY => [
                    'comp' => 'metadata',
                ],
            ])
            ->then(fn () => true)
            ->otherwise(function (mixed $reason) {
                $e = Create::exceptionFor($reason);

                if ($e instanceof QueueStorageException && $e->errorCode === QueueErrorCode::QueueNotFound) {
                    return false;
                }

                throw $e;
            });
    }

    /** Gets queue metadata and approximate message count. */
    public function getProperties(): QueueProperties
    {
        return $this->getPropertiesAsync()->wait();
    }

    /**
     * Asynchronously gets queue metadata and approximate message count.
     *
     * @return PromiseInterface<QueueProperties, mixed>
     */
    public function getPropertiesAsync(): PromiseInterface
    {
        return $this->client
            ->getAsync($this->uri, [
                RequestOptions::QUERY => [
                    'comp' => 'metadata',
                ],
            ])
            ->then(QueueProperties::fromResponseHeaders(...));
    }

    /** Deletes all messages from the queue. */
    public function clearMessages(): void
    {
        $this->clearMessagesAsync()->wait();
    }

    /** Asynchronously deletes all messages from the queue. */
    public function clearMessagesAsync(): PromiseInterface
    {
        return $this->client->deleteAsync($this->messagesUri());
    }

    /**
     * Adds a message to the queue.
     *
     * @param  int|null  $visibilityTimeout  Seconds before the new message becomes visible.
     * @param  int|null  $timeToLive  Message lifetime in seconds; use -1 for no expiry where supported.
     */
    public function sendMessage(string $messageText, ?int $visibilityTimeout = null, ?int $timeToLive = null): SendReceipt
    {
        return $this->sendMessageAsync($messageText, $visibilityTimeout, $timeToLive)->wait();
    }

    /**
     * Asynchronously adds a message to the queue.
     *
     * @return PromiseInterface<SendReceipt, mixed>
     */
    public function sendMessageAsync(string $messageText, ?int $visibilityTimeout = null, ?int $timeToLive = null): PromiseInterface
    {
        $query = [];
        if ($visibilityTimeout !== null) {
            $query['visibilitytimeout'] = $visibilityTimeout;
        }
        if ($timeToLive !== null) {
            $query['messagettl'] = $timeToLive;
        }

        return $this->client
            ->postAsync($this->messagesUri(), [
                RequestOptions::QUERY => $query,
                RequestOptions::BODY => HttpRequestHelper::xml((new QueueMessageRequestBody($messageText))->toXml()),
            ])
            ->then(SendMessageResponseBody::fromResponse(...));
    }

    /**
     * Updates a message's content or visibility timeout using its current pop receipt.
     */
    public function updateMessage(string $messageId, string $popReceipt, int $visibilityTimeout, ?string $messageText = null): UpdateReceipt
    {
        return $this->updateMessageAsync($messageId, $popReceipt, $visibilityTimeout, $messageText)->wait();
    }

    /**
     * Asynchronously updates a message's content or visibility timeout.
     *
     * @return PromiseInterface<UpdateReceipt, mixed>
     */
    public function updateMessageAsync(string $messageId, string $popReceipt, int $visibilityTimeout, ?string $messageText = null): PromiseInterface
    {
        $options = [
            RequestOptions::QUERY => [
                'popreceipt' => $popReceipt,
                'visibilitytimeout' => $visibilityTimeout,
            ],
        ];

        if ($messageText !== null) {
            $options[RequestOptions::BODY] = HttpRequestHelper::xml((new QueueMessageRequestBody($messageText))->toXml());
        }

        return $this->client
            ->putAsync($this->messageUri($messageId), $options)
            ->then(UpdateMessageResponseBody::fromResponse(...));
    }

    /** Deletes a message using the pop receipt from its most recent receive or update operation. */
    public function deleteMessage(string $messageId, string $popReceipt): void
    {
        $this->deleteMessageAsync($messageId, $popReceipt)->wait();
    }

    /** Asynchronously deletes a message using its current pop receipt. */
    public function deleteMessageAsync(string $messageId, string $popReceipt): PromiseInterface
    {
        return $this->client->deleteAsync($this->messageUri($messageId), [
            RequestOptions::QUERY => [
                'popreceipt' => $popReceipt,
            ],
        ]);
    }

    /** Receives the next visible message, or null when the queue has no visible messages. */
    public function receiveMessage(?int $visibilityTimeout = null): ?QueueMessage
    {
        return $this->receiveMessageAsync($visibilityTimeout)->wait();
    }

    /**
     * Asynchronously receives the next visible message.
     *
     * @return PromiseInterface<QueueMessage|null, mixed>
     */
    public function receiveMessageAsync(?int $visibilityTimeout = null): PromiseInterface
    {
        return $this->receiveMessagesAsync(1, $visibilityTimeout)
            ->then(fn (array $messages): ?QueueMessage => $messages[0] ?? null);
    }

    /**
     * Receives up to the requested number of visible messages.
     *
     * @param  int|null  $maxMessages  Maximum messages to receive; Azure Storage accepts 1 through 32.
     * @param  int|null  $visibilityTimeout  Seconds to hide received messages from other consumers.
     * @return QueueMessage[]
     */
    public function receiveMessages(?int $maxMessages = null, ?int $visibilityTimeout = null): array
    {
        return $this->receiveMessagesAsync($maxMessages, $visibilityTimeout)->wait();
    }

    /**
     * Asynchronously receives a batch of visible messages.
     *
     * @return PromiseInterface<array<QueueMessage>, mixed>
     */
    public function receiveMessagesAsync(?int $maxMessages = null, ?int $visibilityTimeout = null): PromiseInterface
    {
        $query = [];
        if ($maxMessages !== null) {
            $query['numofmessages'] = $maxMessages;
        }
        if ($visibilityTimeout !== null) {
            $query['visibilitytimeout'] = $visibilityTimeout;
        }

        return $this->client
            ->getAsync($this->messagesUri(), [
                RequestOptions::QUERY => $query,
            ])
            ->then(ReceiveMessagesResponseBody::fromResponse(...));
    }

    /** Peeks at the next visible message without changing its visibility, or returns null when none is available. */
    public function peekMessage(): ?PeekedMessage
    {
        return $this->peekMessageAsync()->wait();
    }

    /**
     * Asynchronously peeks at the next visible message without changing its visibility.
     *
     * @return PromiseInterface<PeekedMessage|null, mixed>
     */
    public function peekMessageAsync(): PromiseInterface
    {
        return $this->peekMessagesAsync(1)
            ->then(fn (array $messages): ?PeekedMessage => $messages[0] ?? null);
    }

    /**
     * Peeks at up to the requested number of visible messages without changing their visibility.
     *
     * @param  int|null  $maxMessages  Maximum messages to peek; Azure Storage accepts 1 through 32.
     * @return PeekedMessage[]
     */
    public function peekMessages(?int $maxMessages = null): array
    {
        return $this->peekMessagesAsync($maxMessages)->wait();
    }

    /**
     * Asynchronously peeks at a batch of visible messages without changing their visibility.
     *
     * @return PromiseInterface<array<PeekedMessage>, mixed>
     */
    public function peekMessagesAsync(?int $maxMessages = null): PromiseInterface
    {
        $query = [
            'peekonly' => 'true',
        ];
        if ($maxMessages !== null) {
            $query['numofmessages'] = $maxMessages;
        }

        return $this->client
            ->getAsync($this->messagesUri(), [
                RequestOptions::QUERY => $query,
            ])
            ->then(PeekMessagesResponseBody::fromResponse(...));
    }

    private function messagesUri(): UriInterface
    {
        return $this->uri->withPath($this->uri->getPath().'/messages');
    }

    private function messageUri(string $messageId): UriInterface
    {
        return $this->uri->withPath($this->uri->getPath().'/messages/'.$messageId);
    }
}
