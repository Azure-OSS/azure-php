# Azure Storage Common PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/azure-oss/storage-common.svg)](https://packagist.org/packages/azure-oss/storage-common)
[![Packagist Downloads](https://img.shields.io/packagist/dt/azure-oss/storage-common)](https://packagist.org/packages/azure-oss/storage-common)

<img src="https://raw.githubusercontent.com/Azure-OSS/azure-storage-monorepo/main/docs/static/img/logo.svg" width="150" alt="PHP OSS for Azure logo">

Shared authentication, HTTP pipeline, API version, ETag, and SAS primitives used by the Azure Storage PHP SDK packages.

> [!IMPORTANT]
> This package is community-maintained and is not affiliated with, endorsed by, or supported by Microsoft.

## Install

```shell
composer require azure-oss/storage-common
```

## When to use this package

Most applications should install a top-level client package such as `azure-oss/storage-blob` or `azure-oss/storage-queue`.

Install `azure-oss/storage-common` directly when you need low-level storage building blocks such as:

- `StorageSharedKeyCredential`
- `AccountSasBuilder`
- `ApiVersion`
- `HttpClientOptions`
- shared middleware and connection string helpers

## Quickstart

Generate an account SAS token:

```php
<?php

use AzureOss\Storage\Common\Auth\StorageSharedKeyCredential;
use AzureOss\Storage\Common\Sas\AccountSasBuilder;

$credential = new StorageSharedKeyCredential(
    getenv('AZURE_STORAGE_ACCOUNT_NAME'),
    getenv('AZURE_STORAGE_ACCOUNT_KEY'),
);

$sas = AccountSasBuilder::new()
    ->setServices('b')
    ->setResourceTypes('sco')
    ->setPermissions('rl')
    ->setExpiresOn(new DateTimeImmutable('+1 hour'))
    ->build($credential);

echo $sas;
```

## Features

- Shared key authentication primitives for Azure Storage
- Middleware for request IDs, dates, API version headers, retries, and authorization
- Account SAS builders and related value objects
- Connection string and Storage URI helpers
- Shared `ApiVersion` and `ETag` value objects used across Storage packages

## Documentation

You can read the documentation [here](https://php-oss-for-azure.github.io).

## Related packages

- **[azure-oss/storage](https://packagist.org/packages/azure-oss/storage)** — Meta package for the Storage SDKs
- **[azure-oss/identity](https://packagist.org/packages/azure-oss/identity)** — Microsoft Entra ID token authentication
- **[azure-oss/storage-blob](https://packagist.org/packages/azure-oss/storage-blob)** — Blob Storage SDK
- **[azure-oss/storage-blob-flysystem](https://packagist.org/packages/azure-oss/storage-blob-flysystem)** — Flysystem adapter
- **[azure-oss/storage-blob-flysystem-bundle](https://packagist.org/packages/azure-oss/storage-blob-flysystem-bundle)** — Symfony Flysystem bundle
- **[azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)** — Laravel filesystem driver
- **[azure-oss/storage-queue](https://packagist.org/packages/azure-oss/storage-queue)** — Queue Storage SDK
- **[azure-oss/storage-queue-laravel](https://packagist.org/packages/azure-oss/storage-queue-laravel)** — Laravel queue connector
- **[azure-oss/storage-file-share](https://packagist.org/packages/azure-oss/storage-file-share)** — File Share SDK

## Maintenance

This package is part of the community-maintained PHP OSS for Azure project. It is an independent project and is not affiliated with or endorsed by Microsoft.

## License

This project is released under the MIT License. See [LICENSE](./LICENSE) for details.
