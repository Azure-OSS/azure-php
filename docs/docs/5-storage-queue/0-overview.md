---
sidebar_position: 0
slug: /storage-queue/core
title: Overview
---

`azure-oss/storage-queue` is the Azure Storage Queue SDK for PHP.

## Feature list

- Authentication:
  - Connection strings (access keys)
  - Shared key credentials
  - Shared access signatures (SAS) for delegated, time-limited access
  - Microsoft Entra ID (token-based authentication) via azure-oss/azure-identity
- Local development:
  - Supports the Azurite emulator
- Queues:
  - Create, delete, and check existence
  - Read queue properties
  - Clear all messages
- Messages:
  - Send messages (with optional visibility timeout and TTL)
  - Receive one or multiple messages
  - Delete messages
  - Update messages (including visibility timeout)

## Notes

- SAS authentication is supported, but SAS generation is not supported yet in this SDK.
