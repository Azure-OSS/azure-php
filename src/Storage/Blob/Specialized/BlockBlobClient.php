<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Specialized;

use AzureOss\Identity\TokenCredential;
use AzureOss\Storage\Blob\Exceptions\BlobStorageExceptionDeserializer;
use AzureOss\Storage\Blob\Exceptions\InvalidBlobUriException;
use AzureOss\Storage\Blob\Helpers\BlobUriBuilderHelper;
use AzureOss\Storage\Blob\Helpers\BlobUriParserHelper;
use AzureOss\Storage\Blob\Helpers\HashHelper;
use AzureOss\Storage\Blob\Models\BlockBlobClientOptions;
use AzureOss\Storage\Blob\Models\CommitBlockListOptions;
use AzureOss\Storage\Blob\Models\RequestConditionSet;
use AzureOss\Storage\Blob\Models\StageBlockOptions;
use AzureOss\Storage\Blob\Requests\PutBlockRequestBody;
use AzureOss\Storage\Common\Auth\StorageSharedKeyCredential;
use AzureOss\Storage\Common\Helpers\HttpRequestHelper;
use AzureOss\Storage\Common\Middleware\ClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Provides staged upload operations for an Azure Storage block blob.
 */
final class BlockBlobClient
{
    private readonly Client $client;

    public readonly string $containerName;

    public readonly string $blobName;

    /**
     * @throws InvalidBlobUriException
     */
    public function __construct(
        public readonly UriInterface $uri,
        public readonly StorageSharedKeyCredential|TokenCredential|null $credential = null,
        private readonly BlockBlobClientOptions $options = new BlockBlobClientOptions,
    ) {
        $this->containerName = BlobUriParserHelper::getContainerName($uri);
        $this->blobName = BlobUriParserHelper::getBlobName($uri);
        $this->client = (new ClientFactory)->create($uri, $credential, new BlobStorageExceptionDeserializer, $options->httpClientOptions, $options->apiVersion);
    }

    /** Creates a block blob client that targets the specified snapshot without making a service request. */
    public function withSnapshot(?string $snapshot): self
    {
        return new self(
            BlobUriBuilderHelper::withSnapshot($this->uri, $snapshot),
            $this->credential,
            $this->options,
        );
    }

    /** Creates a block blob client that targets the specified blob version without making a service request. */
    public function withVersion(?string $versionId): self
    {
        return new self(
            BlobUriBuilderHelper::withVersion($this->uri, $versionId),
            $this->credential,
            $this->options,
        );
    }

    /** Stages a block for later inclusion in the blob's committed block list. */
    public function stageBlock(string $base64BlockId, StreamInterface|string $content, StageBlockOptions $options = new StageBlockOptions): void
    {
        $this->stageBlockAsync($base64BlockId, $content, $options)->wait();
    }

    /** Asynchronously stages a block for later commitment. */
    public function stageBlockAsync(string $base64BlockId, StreamInterface|string $content, StageBlockOptions $options = new StageBlockOptions): PromiseInterface
    {
        $stream = Utils::streamFor($content);

        $md5 = Utils::hash($stream, 'md5', true);

        return $this->client
            ->putAsync($this->uri, [
                RequestOptions::QUERY => [
                    'comp' => 'block',
                    'blockid' => $base64BlockId,
                ],
                RequestOptions::HEADERS => [
                    'Content-MD5' => HashHelper::serializeMd5($md5),
                    'Content-Length' => (string) $stream->getSize(),
                    ...($options->conditions?->toHeaders(
                        'BlockBlobClient::stageBlock',
                        RequestConditionSet::LEASE_ONLY,
                    ) ?? []),
                ],
                'body' => $content,
            ]);
    }

    /**
     * Commits an ordered list of staged block IDs as the blob's content.
     *
     * @param  string[]  $base64BlockIds
     */
    public function commitBlockList(array $base64BlockIds, CommitBlockListOptions $options = new CommitBlockListOptions): void
    {
        $this->commitBlockListAsync($base64BlockIds, $options)->wait();
    }

    /**
     * Asynchronously commits an ordered list of staged block IDs.
     *
     * @param  string[]  $base64BlockIds
     */
    public function commitBlockListAsync(array $base64BlockIds, CommitBlockListOptions $options = new CommitBlockListOptions): PromiseInterface
    {
        return $this->client
            ->putAsync($this->uri, [
                RequestOptions::QUERY => [
                    'comp' => 'blocklist',
                ],
                RequestOptions::HEADERS => HttpRequestHelper::headers([
                    ...$options->httpHeaders->toArray(),
                    ...($options->conditions?->toHeaders('BlockBlobClient::commitBlockList', RequestConditionSet::ALL) ?? []),
                ]),
                RequestOptions::BODY => HttpRequestHelper::xml((new PutBlockRequestBody($base64BlockIds))->toXml()),
            ]);
    }
}
