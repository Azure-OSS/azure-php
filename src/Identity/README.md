# Azure Identity PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/azure-oss/identity.svg)](https://packagist.org/packages/azure-oss/identity)
[![Packagist Downloads](https://img.shields.io/packagist/dt/azure-oss/identity)](https://packagist.org/packages/azure-oss/identity)

<img src="https://raw.githubusercontent.com/Azure-OSS/azure-storage-monorepo/main/docs/static/img/logo.svg" width="150" alt="PHP OSS for Azure logo">

A PHP identity library for acquiring Microsoft Entra ID access tokens with client secret, client certificate, workload identity, managed identity, and default credential flows.

> [!IMPORTANT]
> This package is community-maintained and is not affiliated with, endorsed by, or supported by Microsoft.

## Install

```shell
composer require azure-oss/identity
```

## Quickstart

```php
<?php

use AzureOss\Identity\ClientSecretCredential;
use AzureOss\Identity\TokenRequestContext;

$credential = new ClientSecretCredential(
    tenantId: getenv('AZURE_TENANT_ID'),
    clientId: getenv('AZURE_CLIENT_ID'),
    clientSecret: getenv('AZURE_CLIENT_SECRET'),
);

$token = $credential->getToken(
    new TokenRequestContext(['https://storage.azure.com/.default'])
);

echo $token->token;
```

## Credentials

- `ClientSecretCredential`
- `ClientCertificateCredential`
- `EnvironmentCredential`
- `WorkloadIdentityCredential`
- `ManagedIdentityCredential`
- `ChainedTokenCredential`
- `DefaultAzureCredential`

## Notes

- `DefaultAzureCredential` tries a configurable credential chain for common Azure hosting environments.
- By default, `DefaultAzureCredential` includes environment and workload identity credentials. Managed identity can be enabled through `DefaultAzureCredentialOptions`.
- The package depends on PSR-18 and PSR-17 interfaces and will use discovered implementations when available.

## Documentation

You can read the documentation [here](https://php-oss-for-azure.github.io).

## Related packages

- **[azure-oss/storage](https://packagist.org/packages/azure-oss/storage)** — Meta package for the Storage SDKs
- **[azure-oss/storage-common](https://packagist.org/packages/azure-oss/storage-common)** — Shared authentication, HTTP, and SAS primitives for Storage SDKs
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
