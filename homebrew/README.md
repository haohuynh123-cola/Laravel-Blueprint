# Homebrew tap setup (one-time)

The actual Homebrew tap lives in a **separate repo** (Homebrew naming convention
requires `homebrew-<name>`). Files in this directory are the source of truth —
the `Update Homebrew tap` workflow copies the formula across on every release.

## One-time setup

### 1. Create the tap repo on GitHub

```bash
gh repo create haohuynh123-cola/homebrew-laravel-blueprint \
  --public \
  --description "Homebrew tap for laravel-blueprint" \
  --add-readme
```

Or via browser at https://github.com/new — name it exactly
`homebrew-laravel-blueprint`.

### 2. Create a Personal Access Token (PAT)

The default `GITHUB_TOKEN` cannot push to other repos. Create a fine-grained PAT:

1. https://github.com/settings/personal-access-tokens/new
2. Repository access → **Only select repositories** → `homebrew-laravel-blueprint`
3. Permissions → **Contents: Read and write**, **Metadata: Read**
4. Generate, copy the token

### 3. Add the token as a repo secret on this repo

```bash
gh secret set HOMEBREW_TAP_TOKEN
# paste the token, hit enter
```

Or browser: **Settings → Secrets and variables → Actions → New repository secret**
named `HOMEBREW_TAP_TOKEN`.

### 4. First release

The next time you push a `v*.*.*` tag, the `Update Homebrew tap` workflow
will commit `Formula/laravel-blueprint.rb` to the tap repo automatically.

If you want to bootstrap the tap with the *current* release without waiting
for a new tag, manually trigger the workflow:

1. https://github.com/haohuynh123-cola/Laravel-Blueprint/actions/workflows/homebrew.yml
2. **Run workflow** → tag `v0.5.0`

## Users install with

```bash
brew tap haohuynh123-cola/laravel-blueprint
brew install laravel-blueprint
```

Updates ride along with `brew upgrade laravel-blueprint` once a new tag fires
the workflow.
