# PHP OSS for Azure

Community-driven PHP SDKs for Azure, because Microsoft won't.

In November 2023, Microsoft officially archived their [Azure SDK for PHP](https://github.com/Azure/azure-sdk-for-php) and stopped maintaining PHP integrations for most Azure services. No migration path, no replacement — just a repository marked read-only.

We picked up where they left off.

<img src="https://php-oss-for-azure.github.io/img/logo.svg" width="150" alt="Screenshot">

## Requirements

- PHP 8.2 or later for the Storage SDK packages; PHP 8.1 or later for Identity
- Guzzle 7 or 8 for the Storage SDK packages

## Documentation

You can read the documentation [here](https://php-oss-for-azure.github.io).

## Development

The root `composer.json`, `phpunit.xml`, and `phpstan.neon` provide the complete local development environment with PHP 8.3 or later, Guzzle 8, and Laravel 13. CI verifies the PHP 8.2, Guzzle 7, and older Laravel compatibility boundaries separately.

Run all tests with `vendor/bin/pest`. To run one side independently, use `vendor/bin/pest --testsuite core` or `vendor/bin/pest --testsuite laravel`.

The storage integration tests expect Azurite on its standard local ports. Start it in a separate terminal before running the tests:

```bash
npx --yes azurite --silent --location .build/azurite
```

```bash
composer update
vendor/bin/pest
vendor/bin/phpstan --no-progress --memory-limit=2G
```

## Package ecosystem

- **[azure-oss/storage](https://packagist.org/packages/azure-oss/storage)** — Meta package for the Storage SDKs
- **[azure-oss/storage-common](https://packagist.org/packages/azure-oss/storage-common)** — Shared authentication, HTTP, and SAS primitives
- **[azure-oss/storage-blob](https://packagist.org/packages/azure-oss/storage-blob)** — Blob Storage SDK
- **[azure-oss/storage-blob-flysystem](https://packagist.org/packages/azure-oss/storage-blob-flysystem)** — Flysystem adapter
- **[azure-oss/storage-blob-flysystem-bundle](https://packagist.org/packages/azure-oss/storage-blob-flysystem-bundle)** — Symfony Flysystem bundle
- **[azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)** — Laravel filesystem driver
- **[azure-oss/storage-queue](https://packagist.org/packages/azure-oss/storage-queue)** — Queue Storage SDK
- **[azure-oss/storage-queue-laravel](https://packagist.org/packages/azure-oss/storage-queue-laravel)** — Laravel queue connector
- **[azure-oss/storage-file-share](https://packagist.org/packages/azure-oss/storage-file-share)** — File Share SDK
- **[azure-oss/identity](https://packagist.org/packages/azure-oss/identity)** — Microsoft Entra ID token authentication

## Acknowledgements

This PHP library takes inspiration from the Azure Storage .NET SDK, particularly in its API design, naming conventions, and overall developer experience.

This project is an independent implementation and is not affiliated with, endorsed by, or maintained by Microsoft.

## License

This project is released under the MIT License. See [LICENSE](./LICENSE) for details.
