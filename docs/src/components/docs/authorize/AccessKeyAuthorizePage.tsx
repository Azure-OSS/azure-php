import React from 'react';
import CodeBlock from '@theme/CodeBlock';

type Props = {
  serviceName: string;
  clientClassName: string;
  clientFqn: string;
  endpointSubdomain: string;
  notes?: string[];
  verifySasCapabilitySnippet?: string;
};

function php(lines: string[]): string {
  return lines.join('\n').trim() + '\n';
}

export default function AccessKeyAuthorizePage({
  serviceName,
  clientClassName,
  clientFqn,
  endpointSubdomain,
  notes,
  verifySasCapabilitySnippet,
}: Props): React.JSX.Element {
  return (
    <>
      <p>Access key authentication is the default when you connect using a storage account connection string.</p>

      <h2>Option 1: Connection String (Recommended)</h2>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          `use ${clientFqn};`,
          '',
          `$service = ${clientClassName}::fromConnectionString(`,
          "    getenv('AZURE_STORAGE_CONNECTION_STRING')",
          ');',
        ])}
      </CodeBlock>

      <p>For local development with Azurite, you can use:</p>

      <CodeBlock language="env">{'AZURE_STORAGE_CONNECTION_STRING="UseDevelopmentStorage=true"\n'}</CodeBlock>

      <p>
        <code>UseDevelopmentStorage=true</code> tells the SDK to connect to the local Azurite emulator instead of Azure
        Storage.
      </p>

      <h2>Option 2: Explicit Endpoint + Shared Key</h2>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          `use ${clientFqn};`,
          'use AzureOss\\Storage\\Common\\Auth\\StorageSharedKeyCredential;',
          'use GuzzleHttp\\Psr7\\Uri;',
          '',
          '$credential = new StorageSharedKeyCredential(',
          "    getenv('AZURE_STORAGE_ACCOUNT_NAME'),",
          "    getenv('AZURE_STORAGE_ACCOUNT_KEY')",
          ');',
          '',
          `$endpoint = new Uri('https://'.getenv('AZURE_STORAGE_ACCOUNT_NAME').'.${endpointSubdomain}.core.windows.net/');`,
          `$service = new ${clientClassName}($endpoint, $credential);`,
        ])}
      </CodeBlock>

      {verifySasCapabilitySnippet !== undefined ? (
        <>
          <h2>Verify SAS Capability</h2>
          <p>With shared key credentials, SAS generation is available:</p>
          <CodeBlock language="php">{verifySasCapabilitySnippet.trim() + '\n'}</CodeBlock>
        </>
      ) : null}

      {notes !== undefined && notes.length > 0 ? (
        <>
          <h2>Notes</h2>
          <ul>
            {notes.map((line) => (
              <li key={line}>{line}</li>
            ))}
          </ul>
        </>
      ) : null}
    </>
  );
}
