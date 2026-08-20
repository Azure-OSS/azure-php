import React from 'react';
import Admonition from '@theme/Admonition';
import CodeBlock from '@theme/CodeBlock';

type Props = {
  serviceName: string;
  clientClassName: string;
  clientFqn: string;
  endpointSubdomain: string;
  dataContributorRole: string;
  dataReaderRole: string;
  notes?: string[];
};

function php(lines: string[]): string {
  return lines.join('\n').trim() + '\n';
}

export default function EntraIdAuthorizePage({
  serviceName,
  clientClassName,
  clientFqn,
  endpointSubdomain,
  dataContributorRole,
  dataReaderRole,
  notes,
}: Props): React.JSX.Element {
  return (
    <>
      <p>Use Microsoft Entra ID when you do not want to use account keys.</p>

      <p>
        This SDK supports token-based authentication via <code>TokenCredential</code>{' '}
        implementations such as <code>DefaultAzureCredential</code>, <code>ClientSecretCredential</code>,{' '}
        <code>ClientCertificateCredential</code> and <code>WorkloadIdentityCredential</code>.
      </p>

      <h2>Prerequisites</h2>

      <p>
        Your identity must have <strong>Azure RBAC</strong> permissions on the storage account (or narrower scope).
        Common roles:
      </p>

      <ul>
        <li>
          <code>{dataContributorRole}</code> (read/write)
        </li>
        <li>
          <code>{dataReaderRole}</code> (read-only)
        </li>
      </ul>

      <h2>Quickstart</h2>

      <p>
        <code>DefaultAzureCredential</code> is the easiest default option.
      </p>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          'use AzureOss\\Identity\\DefaultAzureCredential;',
          `use ${clientFqn};`,
          'use GuzzleHttp\\Psr7\\Uri;',
          '',
          '$credential = new DefaultAzureCredential();',
          '',
          `$endpoint = new Uri('https://'.getenv('AZURE_STORAGE_ACCOUNT_NAME').'.${endpointSubdomain}.core.windows.net/');`,
          `$service = new ${clientClassName}($endpoint, $credential);`,
        ])}
      </CodeBlock>

      <h2>Credentials</h2>

      <h3>DefaultAzureCredential</h3>

      <p>
        <code>DefaultAzureCredential</code> tries these sources in this order and uses the first one that can get a
        token:
      </p>

      <ol>
        <li>
          Environment-based service principal settings: <code>AZURE_TENANT_ID</code>, <code>AZURE_CLIENT_ID</code>, and
          either <code>AZURE_CLIENT_SECRET</code> or <code>AZURE_CLIENT_CERTIFICATE_PATH</code> (optional:{' '}
          <code>AZURE_CLIENT_CERTIFICATE_PASSWORD</code>)
        </li>
        <li>Workload identity</li>
        <li>Managed identity (experimental)</li>
      </ol>

      <p>Example:</p>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          'use AzureOss\\Identity\\DefaultAzureCredential;',
          `use ${clientFqn};`,
          'use GuzzleHttp\\Psr7\\Uri;',
          '',
          '$credential = new DefaultAzureCredential();',
          '',
          `$endpoint = new Uri('https://'.getenv('AZURE_STORAGE_ACCOUNT_NAME').'.${endpointSubdomain}.core.windows.net/');`,
          `$service = new ${clientClassName}($endpoint, $credential);`,
        ])}
      </CodeBlock>

      <h3>Service principal</h3>

      <p>
        If you set the environment variables listed above, <code>DefaultAzureCredential</code> automatically
        authenticates using them.
      </p>

      <p>
        If you prefer to be explicit, use <code>ClientSecretCredential</code>:
      </p>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          'use AzureOss\\Identity\\ClientSecretCredential;',
          `use ${clientFqn};`,
          'use GuzzleHttp\\Psr7\\Uri;',
          '',
          '$credential = new ClientSecretCredential(',
          '    tenantId: getenv(\'AZURE_TENANT_ID\'),',
          '    clientId: getenv(\'AZURE_CLIENT_ID\'),',
          '    clientSecret: getenv(\'AZURE_CLIENT_SECRET\'),',
          ');',
          '',
          `$endpoint = new Uri('https://'.getenv('AZURE_STORAGE_ACCOUNT_NAME').'.${endpointSubdomain}.core.windows.net/');`,
          `$service = new ${clientClassName}($endpoint, $credential);`,
        ])}
      </CodeBlock>

      <p>If you use certificate authentication, use <code>ClientCertificateCredential</code>:</p>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          'use AzureOss\\Identity\\ClientCertificateCredential;',
          `use ${clientFqn};`,
          'use GuzzleHttp\\Psr7\\Uri;',
          '',
          '$credential = new ClientCertificateCredential(',
          '    tenantId: getenv(\'AZURE_TENANT_ID\'),',
          '    clientId: getenv(\'AZURE_CLIENT_ID\'),',
          '    clientCertificatePath: getenv(\'AZURE_CLIENT_CERTIFICATE_PATH\'),',
          '    clientCertificatePassword: getenv(\'AZURE_CLIENT_CERTIFICATE_PASSWORD\') ?: null,',
          ');',
          '',
          `$endpoint = new Uri('https://'.getenv('AZURE_STORAGE_ACCOUNT_NAME').'.${endpointSubdomain}.core.windows.net/');`,
          `$service = new ${clientClassName}($endpoint, $credential);`,
        ])}
      </CodeBlock>

      <h3>Workload identity (federated credentials)</h3>

      <p>
        For Kubernetes or other OIDC-based federation scenarios, use <code>WorkloadIdentityCredential</code>.
      </p>

      <p>Typical configuration uses:</p>

      <ul>
        <li>
          <code>AZURE_TENANT_ID</code>
        </li>
        <li>
          <code>AZURE_CLIENT_ID</code>
        </li>
        <li>
          <code>AZURE_FEDERATED_TOKEN_FILE</code>
        </li>
      </ul>

      <p>Example:</p>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          'use AzureOss\\Identity\\WorkloadIdentityCredential;',
          `use ${clientFqn};`,
          'use GuzzleHttp\\Psr7\\Uri;',
          '',
          '$credential = new WorkloadIdentityCredential();',
          '',
          `$endpoint = new Uri('https://'.getenv('AZURE_STORAGE_ACCOUNT_NAME').'.${endpointSubdomain}.core.windows.net/');`,
          `$service = new ${clientClassName}($endpoint, $credential);`,
        ])}
      </CodeBlock>

      <h3>Managed identity (experimental)</h3>

      <Admonition type="caution" title="Experimental">
        <p>
          <code>ManagedIdentityCredential</code> is experimental. This credential can be difficult to test reliably
          because most managed identity endpoints are only available from within the corresponding Azure runtime (IMDS,
          App Service/Functions, Arc, etc.). If you try this in a real environment and it works (or fails), please let
          us know which environment you used and any relevant HTTP status/error details.
        </p>
      </Admonition>

      <p>Example:</p>

      <CodeBlock language="php">
        {php([
          '<?php',
          '',
          'use AzureOss\\Identity\\ManagedIdentityCredential;',
          `use ${clientFqn};`,
          'use GuzzleHttp\\Psr7\\Uri;',
          '',
          '$credential = new ManagedIdentityCredential();',
          '',
          `$endpoint = new Uri('https://'.getenv('AZURE_STORAGE_ACCOUNT_NAME').'.${endpointSubdomain}.core.windows.net/');`,
          `$service = new ${clientClassName}($endpoint, $credential);`,
        ])}
      </CodeBlock>

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
