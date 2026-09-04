# Changelog

## Unreleased

### Added

- Added `QueueClient::peekMessage()`, `peekMessageAsync()`, `peekMessages()`, and `peekMessagesAsync()` for inspecting visible messages without changing their visibility.

## 1.2.1

### Fixed

- Fixed the README logo so it renders on Packagist.

## 1.2.0

### Changed

- Added support for Guzzle 8 while retaining Guzzle 7 support.
- Async operations now declare their resolved promise result types for static analysis and IDEs.

## 1.1.0

Changes since `1.0.0`.

### Added

- Added per-client Storage API version selection to `QueueServiceClientOptions` and `QueueClientOptions`. The selected version is propagated to queue clients created by a service client.
