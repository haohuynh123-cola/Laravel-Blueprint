'use strict';

/**
 * Downloads the matching blueprint.phar from GitHub Releases and stashes it
 * inside the npm package directory. Called by:
 *  - `postinstall` so `npm install -g` users get the phar up front
 *  - bin/blueprint.js as a fallback if the phar is somehow missing
 *
 * Skipped when:
 *  - BLUEPRINT_SKIP_INSTALL=1 is set (CI / offline)
 *  - The matching phar is already on disk
 */

const fs = require('node:fs');
const path = require('node:path');
const https = require('node:https');
const { pipeline } = require('node:stream/promises');

const PKG = require('../package.json');
const REPO = 'haohuynh123-cola/Laravel-Blueprint';
const TAG = `v${PKG.version}`;
const PHAR_PATH = path.join(__dirname, '..', 'blueprint.phar');
const URL = `https://github.com/${REPO}/releases/download/${TAG}/blueprint.phar`;

async function ensurePhar() {
  if (process.env.BLUEPRINT_SKIP_INSTALL === '1') {
    console.log('[laravel-blueprint] BLUEPRINT_SKIP_INSTALL=1 — skipping phar download.');
    return;
  }

  if (fs.existsSync(PHAR_PATH)) return;

  console.log(`[laravel-blueprint] Downloading ${URL}`);
  await downloadFollowingRedirects(URL, PHAR_PATH);
  fs.chmodSync(PHAR_PATH, 0o755);
  console.log(`[laravel-blueprint] Installed phar at ${PHAR_PATH}`);
}

function downloadFollowingRedirects(url, dest) {
  return new Promise((resolve, reject) => {
    const tryUrl = (current, redirects = 0) => {
      if (redirects > 5) return reject(new Error('Too many redirects'));

      https
        .get(current, (res) => {
          if (res.statusCode === 302 || res.statusCode === 301) {
            res.resume();
            return tryUrl(res.headers.location, redirects + 1);
          }
          if (res.statusCode !== 200) {
            res.resume();
            return reject(
              new Error(
                `Failed to download phar: HTTP ${res.statusCode}. ` +
                  `The release ${TAG} may not exist or the phar asset is missing.`,
              ),
            );
          }
          const file = fs.createWriteStream(dest);
          pipeline(res, file).then(resolve, reject);
        })
        .on('error', reject);
    };
    tryUrl(url);
  });
}

module.exports = { ensurePhar, PHAR_PATH };

// When run directly via `node lib/install.js` (the postinstall script),
// download eagerly. Swallow errors so `npm install` never aborts —
// bin/blueprint.js will retry on first invocation.
if (require.main === module) {
  ensurePhar().catch((err) => {
    console.warn(`[laravel-blueprint] Phar download skipped: ${err.message}`);
  });
}
