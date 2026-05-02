# laravel-blueprint (npm)

> npm wrapper around the [PHP CLI](https://github.com/haohuynh123-cola/Laravel-Blueprint).
> Lets you `npx laravel-blueprint new my-app` without installing anything globally.

## Use

```bash
npx laravel-blueprint new my-app
```

Or install globally:

```bash
npm install -g laravel-blueprint
blueprint new my-app
```

PHP 8.2+ must be on your `PATH` — this package downloads the matching `blueprint.phar`
from the GitHub Release on first install and forwards your CLI args to it.

## How it works

```
npx laravel-blueprint new my-app
        │
        ▼
bin/blueprint.js  ── spawn ──►  php blueprint.phar new my-app
```

- `lib/install.js` runs as `postinstall` and downloads `blueprint.phar` matching
  this package's `version` from the GitHub Release for that tag.
- If the phar is missing at run time, `bin/blueprint.js` re-downloads it.
- Set `BLUEPRINT_SKIP_INSTALL=1` to skip the download (useful in CI / offline).

## Releasing

The version of this package must match the corresponding `vX.Y.Z` tag of the
PHP package. Bump `package.json` `version`, publish:

```bash
cd npm
npm publish --access public
```

## License

MIT — same as the parent project.
