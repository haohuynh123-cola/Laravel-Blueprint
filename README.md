# Laravel Blueprint

> Scaffold production-ready Laravel projects with your choice of starter kit, database, queue, and extras.

Inspired by [`go-blueprint`](https://go-blueprint.dev/), but for Laravel. One command, an interactive wizard (or CI-friendly flags), and you get a runnable project — base install, database wired up, optional Docker / GitHub Actions / starter kit / extras applied for you.

## Status

**Pre-release (v0.0.1).** What works today:

- Interactive wizard with `laravel/prompts` (and `--flag` alternatives for CI)
- Base Laravel install via `composer create-project`
- `.env` rewritten for your chosen database (MySQL, PostgreSQL, SQLite, MariaDB)
- SQLite file created so `php artisan migrate` works out of the box
- `git init` + initial commit (optional)

What's planned (prompts already collect these — generators land next):

- Starter kits: Breeze, Jetstream, Filament
- Frontend stacks: Blade, Livewire, Inertia+Vue, Inertia+React, API
- Extras: Horizon, Telescope, Pulse, Octane, Scout, Sanctum, Pint, Larastan, Dusk, Sail
- Docker: Sail dev environment + production Dockerfile
- CI: GitHub Actions preset (tests, Pint, Larastan)

## Install

```bash
composer global require haohuynh123-cola/laravel-blueprint
```

Make sure `~/.composer/vendor/bin` (or `~/.config/composer/vendor/bin`) is on your `PATH`.

## Usage

### Interactive

```bash
blueprint new
```

Walks you through every choice with arrow keys.

### Non-interactive (CI / scripts)

```bash
blueprint new my-app \
  --kit=breeze \
  --stack=inertia-vue \
  --database=pgsql \
  --tests=pest \
  --extra=horizon --extra=pint --extra=larastan \
  --docker=production \
  --ci=github-actions \
  --git=commit \
  --yes
```

`--yes` skips prompts and falls back to defaults for any flag you omit.

### Available choices

| Flag | Values |
|---|---|
| `--kit` | `none`, `breeze`, `jetstream`, `filament` |
| `--stack` | `blade`, `livewire`, `inertia-vue`, `inertia-react`, `api`, `none` |
| `--database` | `mysql`, `pgsql`, `sqlite`, `mariadb` |
| `--tests` | `pest`, `phpunit` |
| `--extra` | `horizon`, `telescope`, `pulse`, `octane`, `scout`, `sanctum`, `pint`, `larastan`, `dusk`, `sail` (repeatable) |
| `--docker` | `none`, `sail`, `production`, `both` |
| `--ci` | `none`, `github-actions` |
| `--git` | `skip`, `init`, `commit` |

## Develop

```bash
composer install
composer test           # pest
composer lint           # pint
composer stan           # phpstan
./bin/blueprint new     # try it locally
```

## Architecture

```
src/
├── Application.php              # Symfony Console app
├── Commands/NewCommand.php      # Wizard + flag parsing
├── Config/                      # BlueprintConfig + enum types per choice
├── Generators/                  # One class per generator step
│   ├── Generator.php            # interface
│   ├── BaseInstaller.php        # composer create-project
│   ├── DatabaseConfigurator.php # rewrites .env
│   └── GitInitializer.php       # git init [+ commit]
└── Support/ProcessRunner.php    # symfony/process wrapper with streaming output
```

Two design rules borrowed from `go-blueprint`:

1. **Hybrid CLI**: every prompt has a corresponding `--flag` so the same tool works for humans and CI.
2. **Map-of-templaters pattern**: choices are `enum` cases keyed to generators — adding a new database or extra is one enum case + one class.

One we deliberately *changed*: `go-blueprint` keeps templates as Go string literals. We use real stub files under `src/Templates/` so they're lintable, syntax-highlighted, and editable in isolation.

## Roadmap

- v0.1 — starter-kit + Docker + CI generators
- v0.2 — extras (Horizon / Telescope / Pulse / Octane / Sail / Pint / Larastan)
- v0.3 — `blueprint upgrade` for existing projects (apply a single layer to a brownfield repo)
- v0.4 — Phar release + Homebrew tap + `curl | bash` install script
- v0.5 — marketing site at laravel-blueprint.dev

## Contributing

PRs welcome. Each generator is a single class implementing `Generators\Generator`, so adding a new option is small and self-contained. Open an issue first for new flags or stack choices.

## License

MIT
