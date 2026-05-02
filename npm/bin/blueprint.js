#!/usr/bin/env node

/**
 * Thin shim that forwards CLI args to the bundled PHP phar.
 *
 * The phar is downloaded by lib/install.js during `npm install` /
 * `npm exec` / `npx`. If it's missing, we re-download on the fly so
 * the very first `npx laravel-blueprint` works without a separate step.
 */

const { spawn } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const { ensurePhar, PHAR_PATH } = require('../lib/install');

(async () => {
  if (!fs.existsSync(PHAR_PATH)) {
    await ensurePhar();
  }

  const child = spawn('php', [PHAR_PATH, ...process.argv.slice(2)], {
    stdio: 'inherit',
  });

  child.on('error', (err) => {
    if (err.code === 'ENOENT') {
      console.error(
        'PHP is not on your PATH. Install PHP 8.2+ first (e.g. brew install php).',
      );
      process.exit(1);
    }
    console.error(err.message);
    process.exit(1);
  });

  child.on('exit', (code, signal) => {
    if (signal) process.kill(process.pid, signal);
    process.exit(code ?? 1);
  });
})().catch((err) => {
  console.error(err.stack ?? err.message);
  process.exit(1);
});

// Silence unused-symbol lint when path is reserved for future use.
void path;
