# laravel-blueprint.dev

Single-page configurator for the [`laravel-blueprint`](../) CLI. Pick your stack, copy the
generated command, paste into your terminal.

Inspired by [`go-blueprint.dev`](https://go-blueprint.dev).

## Develop

Requires Node 20+.

```bash
cd site
npm install
npm run dev          # http://localhost:5173
```

## Build

```bash
npm run build        # outputs ./dist
npm run preview      # serve the production build locally
```

When deploying to a custom domain (e.g. `laravel-blueprint.dev`), set `VITE_BASE=/`:

```bash
VITE_BASE=/ npm run build
```

The default base is `/Laravel-Blueprint/` so the build works out of the box on
GitHub Pages under the repo name.

## Architecture

```
src/
├── App.tsx                                 Two-panel shell + hero
├── lib/
│   ├── blueprint-options.ts                Choice types + defaults — mirrors src/Config/*.php
│   ├── command-builder.ts                  BlueprintConfig → tokenized terminal output
│   └── tree-builder.ts                     BlueprintConfig → projected directory tree
├── components/
│   ├── options-panel/                      Left column — pick options
│   │   ├── OptionsPanel.tsx
│   │   ├── ChoiceGroup.tsx                 Single-select radio cards
│   │   ├── ExtrasGroup.tsx                 Multi-select checkbox cards
│   │   └── ProjectNameField.tsx
│   └── preview-panel/                      Right column — copy-ready output
│       ├── PreviewPanel.tsx
│       ├── CommandBlock.tsx                Syntax-highlighted command + copy button
│       └── DirectoryTree.tsx               Projected project tree with tag legend
└── styles/
    ├── tokens.css                          Palette / typography / spacing variables
    └── global.css                          Reset + base styles
```

### Keeping the model in sync with the CLI

`src/lib/blueprint-options.ts` is the canonical TypeScript mirror of
`../src/Config/*.php`. When you add or rename a CLI flag, update **both** sides
in the same commit.
