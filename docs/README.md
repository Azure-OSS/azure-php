# Website

> [!NOTE]
> Development happens in the [Azure PHP monorepo](https://github.com/php-oss-for-azure/azure-php). Please report issues, request features, and submit pull requests there.

This website is built using [Docusaurus](https://docusaurus.io/), a modern static website generator.

> [!IMPORTANT]
> PHP OSS for Azure is a community-maintained project and is not affiliated with, endorsed by, or supported by Microsoft.

## Installation

```bash
yarn
```

## Local Development

```bash
yarn start
```

This command starts a local development server and opens up a browser window. Most changes are reflected live without having to restart the server.

## Build

```bash
yarn build
```

This command generates static content into the `build` directory and can be served using any static contents hosting service.

## PHP API Reference

Generate the standalone phpDocumentor site from the repository root before building Docusaurus:

```bash
composer docs:api
```

The generated files are written to `docs/static/api/`, copied into the Docusaurus build, and served at `/api/` under the configured site base URL. The generated files are ignored by Git and are rebuilt by the documentation GitHub Actions workflow.

## Deployment

Documentation releases are deployed automatically through GitHub Pages. Create and push a documentation tag from the monorepo:

```bash
git tag docs-1.2.3
git push origin docs-1.2.3
```

The subtree workflow publishes the `docs/` tree and the split-repository tag `1.2.3` to `php-oss-for-azure/php-oss-for-azure.github.io`. That tag starts the Pages workflow, which checks out the matching `docs-1.2.3` monorepo tag, generates the PHP API reference, builds Docusaurus, and deploys the build as an artifact. Generated API and Docusaurus build files are never committed.

The Pages repository must use **GitHub Actions** as its publishing source under **Settings → Pages**.

## License

This project is released under the MIT License. See [LICENSE](./LICENSE) for details.
