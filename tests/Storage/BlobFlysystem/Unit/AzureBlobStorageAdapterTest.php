<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\BlobFlysystem\Unit;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\Models\BlobContainerClientOptions;
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use AzureOss\Storage\Common\Auth\StorageSharedKeyCredential;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Uri;
use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\Config;
use League\Flysystem\UnableToGenerateTemporaryUrl;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\TestCase;

final class AzureBlobStorageAdapterTest extends TestCase
{
    public function test_set_visibility_throws_by_default(): void
    {
        $adapter = $this->createAdapter();

        $this->expectException(UnableToSetVisibility::class);
        $this->expectExceptionMessage('Azure does not support this operation.');

        $adapter->setVisibility('docs/readme.md', 'public');
    }

    public function test_set_visibility_can_be_ignored(): void
    {
        $adapter = $this->createAdapter(
            visibilityHandling: AzureBlobStorageAdapter::ON_VISIBILITY_IGNORE,
        );

        $this->expectNotToPerformAssertions();

        $adapter->setVisibility('docs/readme.md', 'public');
    }

    public function test_write_wraps_invalid_transfer_size_configuration(): void
    {
        $adapter = $this->createAdapter();

        try {
            $adapter->write('docs/readme.md', 'content', new Config([
                'initialTransferSize' => 'invalid',
            ]));
            self::fail('Expected invalid transfer size configuration to fail.');
        } catch (UnableToWriteFile $exception) {
            self::assertSame(
                'initialTransferSize must be an int.',
                $exception->getPrevious()?->getMessage(),
            );
            self::assertSame(
                'initialTransferSize must be an int.',
                $exception->reason(),
            );
        }
    }

    public function test_write_wraps_invalid_conditional_request_configuration(): void
    {
        $adapter = $this->createAdapter();

        try {
            $adapter->write('docs/readme.md', 'content', new Config([
                'conditions' => [
                    'ifMatch' => 123,
                ],
            ]));
            self::fail('Expected invalid conditional request configuration to fail.');
        } catch (UnableToWriteFile $exception) {
            self::assertSame(
                'conditions.ifMatch must be a string.',
                $exception->getPrevious()?->getMessage(),
            );
        }
    }

    public function test_public_url_uses_the_prefixed_blob_uri_for_public_containers(): void
    {
        $adapter = $this->createAdapter(prefix: 'tenant-a', isPublicContainer: true);

        $url = $adapter->publicUrl('reports/annual.pdf', new Config);

        self::assertSame(
            'http://127.0.0.1:10000/devstoreaccount1/test-container/tenant-a/reports/annual.pdf',
            $url,
        );
    }

    public function test_public_url_falls_back_to_a_long_lived_sas_for_private_containers(): void
    {
        $adapter = $this->createAdapter(prefix: 'tenant-a');

        $url = $adapter->publicUrl('reports/annual.pdf', new Config);
        $query = $this->parseQueryString($url);

        self::assertSame('r', $query['sp'] ?? null);
        self::assertArrayHasKey('se', $query);
        self::assertArrayHasKey('sig', $query);
        self::assertStringContainsString(
            '/test-container/tenant-a/reports/annual.pdf',
            $url,
        );
    }

    public function test_temporary_url_validates_permissions_option_type(): void
    {
        $adapter = $this->createAdapter();

        $this->expectException(UnableToGenerateTemporaryUrl::class);
        $this->expectExceptionMessage('permissions must be a string!');

        $adapter->temporaryUrl(
            'docs/readme.md',
            new \DateTimeImmutable('+5 minutes'),
            new Config([
                'permissions' => ['read'],
            ]),
        );
    }

    public function test_temporary_url_validates_http_header_option_type(): void
    {
        $adapter = $this->createAdapter();

        $this->expectException(UnableToGenerateTemporaryUrl::class);
        $this->expectExceptionMessage('contentType must be a string!');

        $adapter->temporaryUrl(
            'docs/readme.md',
            new \DateTimeImmutable('+5 minutes'),
            new Config([
                'httpHeaders' => [
                    'contentType' => ['text/plain'],
                ],
            ]),
        );
    }

    public function test_temporary_url_embeds_requested_permissions_and_response_headers(): void
    {
        $adapter = $this->createAdapter(prefix: 'tenant-a');

        $url = $adapter->temporaryUrl(
            'reports/annual.pdf',
            new \DateTimeImmutable('+5 minutes'),
            new Config([
                'permissions' => 'rw',
                'httpHeaders' => [
                    'cacheControl' => 'public, max-age=60',
                    'contentDisposition' => 'attachment; filename="annual.pdf"',
                    'contentEncoding' => 'identity',
                    'contentLanguage' => 'en-US',
                    'contentType' => 'application/pdf',
                ],
            ]),
        );

        $query = $this->parseQueryString($url);

        self::assertSame('rw', $query['sp'] ?? null);
        self::assertSame('public, max-age=60', $query['rscc'] ?? null);
        self::assertSame('attachment; filename="annual.pdf"', $query['rscd'] ?? null);
        self::assertSame('identity', $query['rsce'] ?? null);
        self::assertSame('en-US', $query['rscl'] ?? null);
        self::assertSame('application/pdf', $query['rsct'] ?? null);
        self::assertStringContainsString(
            '/test-container/tenant-a/reports/annual.pdf',
            $url,
        );
    }

    public function test_checksum_rejects_unsupported_algorithms_before_contacting_azure(): void
    {
        $adapter = $this->createAdapter();

        $this->expectException(ChecksumAlgoIsNotSupported::class);

        $adapter->checksum('docs/readme.md', new Config([
            'checksum_algo' => 'sha256',
        ]));
    }

    private function createAdapter(
        string $prefix = '',
        string $visibilityHandling = AzureBlobStorageAdapter::ON_VISIBILITY_THROW_ERROR,
        bool $isPublicContainer = false,
    ): AzureBlobStorageAdapter {
        return new AzureBlobStorageAdapter(
            new BlobContainerClient(
                new Uri('http://127.0.0.1:10000/devstoreaccount1/test-container'),
                new StorageSharedKeyCredential(
                    'devstoreaccount1',
                    base64_encode('test-key-material'),
                ),
                new BlobContainerClientOptions,
            ),
            $prefix,
            visibilityHandling: $visibilityHandling,
            isPublicContainer: $isPublicContainer,
        );
    }

    /**
     * @return array<string, string>
     */
    private function parseQueryString(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        self::assertIsString($query);

        $parsed = Query::parse($query);
        $normalized = [];

        foreach ($parsed as $key => $value) {
            if (! is_string($key) || ! is_string($value)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
