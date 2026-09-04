# Azure Storage Queue driver for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/azure-oss/storage-queue-laravel.svg)](https://packagist.org/packages/azure-oss/storage-queue-laravel)
[![Packagist Downloads](https://img.shields.io/packagist/dt/azure-oss/storage-queue-laravel)](https://packagist.org/packages/azure-oss/storage-queue-laravel)

<img src="https://raw.githubusercontent.com/Azure-OSS/azure-storage-monorepo/main/docs/static/img/logo.svg" width="150" alt="PHP OSS for Azure logo">

A Laravel queue driver for Azure Queue Storage built on top of `azure-oss/storage-queue`.

> [!IMPORTANT]
> This package is community-maintained and is not affiliated with, endorsed by, or supported by Microsoft.

## Install

```shell
composer require azure-oss/storage-queue-laravel
```

## Configuration

Add a connection to `config/queue.php`:

```php
'connections' => [
    'azure' => [
        'driver' => 'azure-storage-queue',
        'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
        'queue' => env('AZURE_STORAGE_QUEUE', 'default'),
        'retry_after' => 60,
        'time_to_live' => null,
        'create_queue' => false,
    ],
],
```

This connector supports shared key and SAS-based authentication via `connection_string`, or shared key via `account_name` + `account_key`. See the [installation docs](https://php-oss-for-azure.github.io/category/storage-queue-laravel/installation) for configuration examples.

## Documentation

You can read the documentation [here](https://php-oss-for-azure.github.io/category/storage-queue-laravel).

## Migration Guide

Migrating from `squigg/azure-queue-laravel`?
[Migrate from squigg/azure-queue-laravel](https://php-oss-for-azure.github.io/storage-queue-laravel/migrate-from-squigg-azure-queue-laravel).

## Related packages

- **[azure-oss/storage](https://packagist.org/packages/azure-oss/storage)** — Meta package for the Storage SDKs
- **[azure-oss/storage-common](https://packagist.org/packages/azure-oss/storage-common)** — Shared authentication, HTTP, and SAS primitives
- **[azure-oss/storage-queue](https://packagist.org/packages/azure-oss/storage-queue)** — Queue Storage SDK
- **[azure-oss/storage-blob](https://packagist.org/packages/azure-oss/storage-blob)** — Blob Storage SDK
- **[azure-oss/storage-blob-flysystem](https://packagist.org/packages/azure-oss/storage-blob-flysystem)** — Flysystem adapter
- **[azure-oss/storage-blob-flysystem-bundle](https://packagist.org/packages/azure-oss/storage-blob-flysystem-bundle)** — Symfony Flysystem bundle
- **[azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)** — Laravel filesystem driver
- **[azure-oss/storage-file-share](https://packagist.org/packages/azure-oss/storage-file-share)** — File Share SDK
- **[azure-oss/identity](https://packagist.org/packages/azure-oss/identity)** — Microsoft Entra ID token authentication

## Maintenance

This package is part of the community-maintained PHP OSS for Azure project. It is an independent project and is not affiliated with or endorsed by Microsoft.

## License

This project is released under the MIT License. See [LICENSE](./LICENSE) for details.
