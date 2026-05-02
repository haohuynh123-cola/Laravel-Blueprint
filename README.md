# Laravel Blueprint

> **One command, your Laravel stack.** Pick a starter kit, database, and the extras you actually use — get a runnable, opinionated, production-ready Laravel project in seconds.

Inspired by [`go-blueprint`](https://go-blueprint.dev/), but for Laravel.

🌐 **Live configurator**: [haohuynh123-cola.github.io/Laravel-Blueprint](https://haohuynh123-cola.github.io/Laravel-Blueprint/) — click options, copy the command.

📦 **Packagist**: [`haohuynh123-cola/laravel-blueprint`](https://packagist.org/packages/haohuynh123-cola/laravel-blueprint)

---

## Quick start — pick the install that fits you

### `curl | bash` — no Composer needed, just PHP

```bash
curl -sSL https://raw.githubusercontent.com/haohuynh123-cola/Laravel-Blueprint/main/install.sh | bash
blueprint new
```

Downloads the single-file phar from the latest GitHub Release and drops it into `/usr/local/bin/blueprint`. Override with `INSTALL_DIR=~/bin VERSION=v0.4.0 bash` if needed.

### `npx` — no install at all

```bash
npx laravel-blueprint new my-app
```

Spawns the phar via Node — handy if you're already in a JS project. Requires PHP 8.2+ on your `PATH`.

### Homebrew — macOS / Linux

```bash
brew tap haohuynh123-cola/laravel-blueprint
brew install laravel-blueprint
blueprint new
```

Pulls PHP if missing, installs the matching phar, puts `blueprint` on your `PATH`. Update with `brew upgrade laravel-blueprint`.

### Docker — zero PHP install

```bash
docker run --rm -it -v "$PWD:/work" -w /work \
  ghcr.io/haohuynh123-cola/laravel-blueprint new my-app
```

Image bundles PHP 8.3 + Composer + Node + git. Multi-arch (amd64 + arm64). Tag `latest` or pin `:0.5.0`. The bind-mount drops the generated project in your current directory.

### Composer — the canonical PHP way

```bash
composer global require haohuynh123-cola/laravel-blueprint
blueprint new
```

> **PATH**: if `blueprint` says `command not found`, add Composer's global bin:
> ```bash
> echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.zshrc && source ~/.zshrc
> ```

The wizard walks you through every choice, then scaffolds the project.

---

## What you get

A Laravel project that's already wired up the way you wanted — no manual `composer require X && php artisan Y:install` chains.

- **Base install** — current stable Laravel via `composer create-project`
- **Starter kits** — Breeze · Jetstream · Filament (each installed and configured for your chosen frontend)
- **Frontend stacks** — Blade · Livewire · Inertia + Vue · Inertia + React · API only
- **Database** — MySQL · PostgreSQL · SQLite · MariaDB (`.env` rewritten, SQLite file created)
- **Cache** — Database · File · Redis · Memcached
- **Queue** — Sync · Database · Redis · Beanstalkd · SQS · RabbitMQ (Redis/RabbitMQ auto-install their driver packages)
- **Extras** — Horizon · Telescope · Pulse · Octane · Scout · Sanctum · Pint · Larastan · Dusk · Sail
- **Docker** — production Dockerfile + nginx + php.ini, or Sail dev environment, or both
- **CI** — GitHub Actions workflow (tests + Pint + PHPStan)
- **Git** — `git init` + initial commit (optional)

---

## How it looks (interactive wizard)

```
┌ Laravel Blueprint — scaffold a new Laravel project ─┐

◇ Project name                  ← type a name
│ my-app

◇ Starter kit                    ← ↑↓ then enter
│ ● None — bare Laravel
│ ○ Breeze — minimal auth scaffold
│ ○ Jetstream — teams, 2FA, profile
│ ○ Filament — admin panel

◇ Frontend stack                 ← shown only if kit ≠ none
│ ...

◇ Database                       ← ↑↓ then enter
│ ● SQLite (zero setup)
│ ○ MySQL
│ ○ PostgreSQL
│ ○ MariaDB

◇ Test runner                    ← Pest or PHPUnit

◇ Extras                         ← MULTI-SELECT: space toggles, enter confirms
│ ◼ Pint
│ ◼ Larastan
│ ◻ Horizon
│ ◻ Telescope
│ ...

◇ Docker
│ ● None  ○ Sail  ○ Production  ○ Both

◇ Continuous integration
│ ○ None  ● GitHub Actions

◇ Initialize git
│ ○ Skip  ○ git init  ● git init + commit

└──────────────────────────────────────────────────────┘
```

After the last prompt, the tool runs every selected generator and leaves you with a project ready to `php artisan serve`.

---

## Non-interactive (CI / scripts)

Every prompt has a `--flag`. Pass `--yes` to skip prompts and use defaults for any flag you omit.

```bash
blueprint new my-app \
  --kit=breeze \
  --stack=inertia-vue \
  --database=pgsql \
  --tests=pest \
  --extra=pint --extra=larastan --extra=horizon \
  --docker=production \
  --ci=github-actions \
  --git=commit \
  --yes
```

### All flags

| Flag | Values |
|---|---|
| `--kit` | `none`, `breeze`, `jetstream`, `filament` |
| `--stack` | `blade`, `livewire`, `inertia-vue`, `inertia-react`, `api`, `none` |
| `--database` | `mysql`, `pgsql`, `sqlite`, `mariadb` |
| `--cache` | `database`, `file`, `redis`, `memcached` |
| `--queue` | `sync`, `database`, `redis`, `beanstalkd`, `sqs`, `rabbitmq` |
| `--tests` | `pest`, `phpunit` |
| `--extra` | `horizon`, `telescope`, `pulse`, `octane`, `scout`, `sanctum`, `pint`, `larastan`, `dusk`, `sail` (repeatable) |
| `--docker` | `none`, `sail`, `production`, `both` |
| `--ci` | `none`, `github-actions` |
| `--git` | `skip`, `init`, `commit` |
| `--yes` / `-y` | Skip prompts |

---

## Add layers to an existing Laravel project

Already have a Laravel app and want to bolt on Docker, GitHub Actions, or an extra? Use `blueprint add`:

```bash
cd existing-laravel-app

blueprint add --extra=horizon --extra=pulse              # add packages
blueprint add --docker=production                         # write Dockerfile + nginx.conf + php.ini
blueprint add --ci=github-actions                         # write .github/workflows/*.yml
blueprint add --extra=pint --extra=larastan --ci=github-actions   # combine
```

`blueprint add` refuses to overwrite existing files (Dockerfile, workflows). Pass `--force` to replace them. It also refuses to run in directories that aren't Laravel projects (no `artisan` + no `laravel/framework` in `composer.json`).

### Add flags

| Flag | Values |
|---|---|
| `--extra` | Same as `new` (repeatable) |
| `--cache` | Same as `new` |
| `--queue` | Same as `new` |
| `--docker` | `none`, `sail`, `production`, `both` |
| `--ci` | `none`, `github-actions` |
| `--database` | Used by Sail install only |
| `--force` / `-f` | Overwrite existing Docker / CI files |

---

## Recipes

### Smallest possible project (~20 sec)

```bash
blueprint new demo --database=sqlite --git=skip --yes
cd demo && php artisan serve
```

### API + Sanctum + Pest + GH Actions

```bash
blueprint new api \
  --kit=breeze --stack=api \
  --database=pgsql --tests=pest \
  --extra=sanctum --extra=pint --extra=larastan \
  --ci=github-actions --git=commit --yes
```

### High-throughput app with Redis cache + RabbitMQ queue

```bash
blueprint new orders \
  --kit=breeze --stack=inertia-vue \
  --database=pgsql --cache=redis --queue=rabbitmq \
  --tests=pest --extra=horizon --extra=pulse \
  --docker=both --ci=github-actions --git=commit --yes
```

### Full-stack Inertia + Vue + production Docker

```bash
blueprint new shop \
  --kit=breeze --stack=inertia-vue \
  --database=pgsql --tests=pest \
  --extra=horizon --extra=pulse --extra=pint --extra=larastan \
  --docker=production --ci=github-actions --git=commit --yes
```

---

## Install (other ways)

### Per-project install (binary in `vendor/bin/blueprint`)

```bash
composer require --dev haohuynh123-cola/laravel-blueprint
vendor/bin/blueprint new
```

### From source (for contributing)

```bash
git clone https://github.com/haohuynh123-cola/Laravel-Blueprint.git
cd Laravel-Blueprint
composer install
./bin/blueprint new
```

---

## Update / uninstall

```bash
composer global update haohuynh123-cola/laravel-blueprint    # update
composer global remove haohuynh123-cola/laravel-blueprint    # uninstall
```

---

## Develop

```bash
composer install
composer test     # Pest
composer lint     # Pint
composer stan     # PHPStan level 8
```

---

## Architecture

```
src/
├── Application.php              Symfony Console app
├── Commands/NewCommand.php      Wizard + flag parsing
├── Config/                      BlueprintConfig + enum types per choice
├── Generators/                  One class per generator step
│   ├── Generator.php            interface
│   ├── BaseInstaller.php        composer create-project laravel/laravel
│   ├── DatabaseConfigurator.php rewrites .env
│   ├── StarterKitGenerator.php  composer require + artisan {kit}:install
│   ├── ExtrasGenerator.php      map-driven: one entry per extra
│   ├── DockerGenerator.php      writes Dockerfile/nginx/php.ini, or Sail
│   ├── CiGenerator.php          writes .github/workflows/*.yml
│   └── GitInitializer.php       git init [+ commit]
├── Support/
│   ├── ProcessRunner.php        symfony/process wrapper, streams output
│   └── StubLoader.php           {{ var }} substitution for stub files
└── Templates/                   Real .stub files — lintable, syntax-highlighted
    ├── docker/                  Dockerfile, nginx.conf, php.ini, .dockerignore
    └── ci/                      tests.yml, lint.yml
```

Two design rules borrowed from `go-blueprint`:

1. **Hybrid CLI** — every prompt has a `--flag`, so the same binary works for humans and CI.
2. **Map-of-templaters** — choices are `enum` cases keyed to generators. Adding a new database or extra is one enum case + one map entry.

One we deliberately changed: `go-blueprint` keeps templates as Go string literals. We use real `.stub` files under `src/Templates/` so they stay syntax-highlighted, lintable, and editable in isolation.

---

## Roadmap

- ✅ **v0.0.1** — base install + database + git
- ✅ **v0.2.0** — starter kit + extras + Docker + CI generators
- ✅ **v0.3.0** — `blueprint add` for existing projects
- ✅ **v0.4.0** — Phar release + `curl | bash` install script + npm wrapper for `npx`
- ✅ **v0.5.0** — Homebrew tap + Docker image (zero-PHP install)
- ✅ **v0.6.0** — Cache + Queue drivers (Redis, RabbitMQ, Memcached, etc.) *(current)*
- ✅ **Configurator site** — live at [haohuynh123-cola.github.io/Laravel-Blueprint](https://haohuynh123-cola.github.io/Laravel-Blueprint/)

---

## Contributing

PRs welcome. Each generator is a single class implementing `Generators\Generator`, so adding a new option is small and self-contained. Open an issue first for new flags or stack choices.

---

## License

MIT — see [LICENSE](LICENSE).
