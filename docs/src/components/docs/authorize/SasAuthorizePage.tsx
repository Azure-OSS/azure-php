import React from 'react';
import CodeBlock from '@theme/CodeBlock';

type UsingScope = {
  title: string;
  description?: string;
  language?: string;
  code: string;
};

type GenerationSection = {
  title: string;
  language?: string;
  code: string;
};

type Props = {
  supportsGeneration: boolean;
  usingIntro?: string;
  usingScopes: UsingScope[];
  accountIntro?: string;
  connectionStringIntro: string;
  connectionStringEnvExample: string;
  connectionStringPhpExample: string;
  endpointUrlIntro: string;
  endpointUrlPhpExample: string;
  serviceClientUsageIntro?: string;
  serviceClientUsagePhpExample?: string;
  generationIntro?: string;
  generationSections?: GenerationSection[];
  generationNotSupportedText?: string;
};

function codeBlock(language: string, code: string): React.JSX.Element {
  return <CodeBlock language={language}>{code.trim() + '\n'}</CodeBlock>;
}

export default function SasAuthorizePage({
  supportsGeneration,
  usingIntro,
  usingScopes,
  accountIntro,
  connectionStringIntro,
  connectionStringEnvExample,
  connectionStringPhpExample,
  endpointUrlIntro,
  endpointUrlPhpExample,
  serviceClientUsageIntro,
  serviceClientUsagePhpExample,
  generationIntro,
  generationSections,
  generationNotSupportedText,
}: Props): React.JSX.Element {
  return (
    <>
      <p>SAS (Shared Access Signature) lets you grant scoped, time-limited access without exposing your account key.</p>

      <h2>Using SAS (authenticate)</h2>

      {usingIntro !== undefined ? <p>{usingIntro}</p> : null}

      {usingScopes.map((scope) => (
        <React.Fragment key={scope.title}>
          <h3>{scope.title}</h3>
          {scope.description !== undefined ? <p>{scope.description}</p> : null}
          {codeBlock(scope.language ?? 'php', scope.code)}
        </React.Fragment>
      ))}

      <h3>Account (Account SAS / Service SAS on the account endpoint)</h3>

      {accountIntro !== undefined && accountIntro.trim() !== '' ? <p>{accountIntro}</p> : null}

      <p>Option 1: SAS connection string</p>
      <p>{connectionStringIntro}</p>
      {codeBlock('env', connectionStringEnvExample)}
      {codeBlock('php', connectionStringPhpExample)}

      <p>Option 2: SAS endpoint URL</p>
      <p>{endpointUrlIntro}</p>
      {codeBlock('php', endpointUrlPhpExample)}

      {serviceClientUsagePhpExample !== undefined ? (
        <>
          <p>{serviceClientUsageIntro ?? 'Use it like a normal service client:'}</p>
          {codeBlock('php', serviceClientUsagePhpExample)}
        </>
      ) : null}

      <h2>Generating SAS</h2>

      {generationIntro !== undefined && generationIntro.trim() !== '' ? <p>{generationIntro}</p> : null}

      {supportsGeneration ? (
        <>
          {generationSections?.map((section) => (
            <React.Fragment key={section.title}>
              <h3>{section.title}</h3>
              {codeBlock(section.language ?? 'php', section.code)}
            </React.Fragment>
          ))}
        </>
      ) : (
        <p>{generationNotSupportedText ?? 'SAS generation is not supported yet in this SDK.'}</p>
      )}
    </>
  );
}
