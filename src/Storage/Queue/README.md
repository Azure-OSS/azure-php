# Azure Storage Queue PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/azure-oss/storage-queue.svg)](https://packagist.org/packages/azure-oss/storage-queue)
[![Packagist Downloads](https://img.shields.io/packagist/dt/azure-oss/storage-queue)](https://packagist.org/packages/azure-oss/storage-queue)

A PHP SDK for Azure Queue Storage with support for queue management, message send/receive/delete, and SAS generation.

> [!IMPORTANT]
> This package is community-maintained and is not affiliated with, endorsed by, or supported by Microsoft.

## Install

```shell
composer require azure-oss/storage-queue
```

## Quickstart

```php
<?php

use AzureOss\Storage\Queue\QueueServiceClient;

$service = QueueServiceClient::fromConnectionString(
    getenv('AZURE_STORAGE_CONNECTION_STRING')
);

$queue = $service->getQueueClient('quickstart');
$queue->createIfNotExists();

$queue->sendMessage('Hello from PHP OSS for Azure');

$message = $queue->receiveMessage(30);
if ($message !== null) {
    echo $message->messageText.PHP_EOL;
    $queue->deleteMessage($message->messageId, $message->popReceipt);
}

// Optional cleanup
$queue->deleteIfExists();
```

## Features

- Authentication:
  - Connection strings (access keys)
  - Shared key credentials
  - Shared access signatures (SAS) for delegated, time-limited access
  - Microsoft Entra ID (token-based authentication) via azure-oss/azure-identity
- Queues:
  - Create, delete, and check existence
  - Read properties
  - Clear messages
- Messages:
  - Send messages (with optional visibility timeout and TTL)
  - Receive one or multiple messages (with visibility timeout)
  - Delete messages
  - Update messages (including visibility timeout)

## Documentation

You can read the documentation [here](https://php-oss-for-azure.github.io).

## Migration Guide

Migrating from `microsoft/azure-storage-queue`?
[Migrate from microsoft/azure-storage-queue](https://php-oss-for-azure.github.io/storage-queue/migrate-from-microsoft-azure-storage-queue).

## Related packages

- **[azure-oss/storage](https://packagist.org/packages/azure-oss/storage)** — Meta package for the Storage SDKs
- **[azure-oss/storage-common](https://packagist.org/packages/azure-oss/storage-common)** — Shared authentication, HTTP, and SAS primitives
- **[azure-oss/storage-blob](https://packagist.org/packages/azure-oss/storage-blob)** — Blob Storage SDK
- **[azure-oss/storage-blob-flysystem](https://packagist.org/packages/azure-oss/storage-blob-flysystem)** — Flysystem adapter
- **[azure-oss/storage-blob-flysystem-bundle](https://packagist.org/packages/azure-oss/storage-blob-flysystem-bundle)** — Symfony Flysystem bundle
- **[azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)** — Laravel filesystem driver
- **[azure-oss/storage-queue-laravel](https://packagist.org/packages/azure-oss/storage-queue-laravel)** — Laravel queue connector
- **[azure-oss/storage-file-share](https://packagist.org/packages/azure-oss/storage-file-share)** — File Share SDK
- **[azure-oss/identity](https://packagist.org/packages/azure-oss/identity)** — Microsoft Entra ID token authentication

## Maintenance

This package is part of the community-maintained PHP OSS for Azure project. It is an independent project and is not affiliated with or endorsed by Microsoft.

## License

This project is released under the MIT License. See [LICENSE](./LICENSE) for details.
