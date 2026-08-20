# Changelog

## Unreleased

No user-facing changes since `1.0.3`.

## 1.0.3

### Changed

- Added an internal `ManagedIdentityCredentialOptions::$imdsEndpoint` override to support deterministic managed identity testing and advanced hosting scenarios without changing the default IMDS behavior.
- Added Guzzle 8 development support while retaining Guzzle 7 support.
