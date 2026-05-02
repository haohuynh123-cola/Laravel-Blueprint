import { useState } from 'react';
import { OptionsPanel } from './components/options-panel/OptionsPanel';
import { PreviewPanel } from './components/preview-panel/PreviewPanel';
import { DEFAULT_CONFIG, type BlueprintConfig } from './lib/blueprint-options';
import './app.css';

export function App() {
  const [config, setConfig] = useState<BlueprintConfig>(DEFAULT_CONFIG);

  return (
    <div className="app">
      <header className="app__header">
        <div className="app__brand">
          <span className="app__logo" aria-hidden="true">
            ⌘
          </span>
          <div>
            <p className="app__brand-name">Laravel Blueprint</p>
            <p className="app__brand-tag">scaffold a production-ready Laravel project</p>
          </div>
        </div>
        <nav aria-label="External links" className="app__nav">
          <a
            href="https://github.com/haohuynh123-cola/Laravel-Blueprint"
            target="_blank"
            rel="noreferrer"
          >
            GitHub ↗
          </a>
        </nav>
      </header>

      <main className="app__main">
        <section className="app__hero">
          <h1 className="app__hero-title">
            One command. <em>Your</em> Laravel stack.
          </h1>
          <p className="app__hero-lede">
            Pick a starter kit, database, and the extras you actually use. Copy the generated
            command, paste it into your terminal, and your project is ready before your coffee.
          </p>
        </section>

        <div className="app__columns">
          <OptionsPanel config={config} onChange={setConfig} />
          <PreviewPanel config={config} />
        </div>
      </main>

      <footer className="app__footer">
        <p>
          MIT licensed · inspired by{' '}
          <a href="https://go-blueprint.dev" target="_blank" rel="noreferrer">
            go-blueprint
          </a>{' '}
          · made for the Laravel community
        </p>
      </footer>
    </div>
  );
}
