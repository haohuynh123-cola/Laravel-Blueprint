import {
  CACHES,
  CI_PRESETS,
  DATABASES,
  DEFAULT_CONFIG,
  DOCKER_MODES,
  EXTRAS,
  FRONTEND_STACKS,
  GIT_MODES,
  QUEUES,
  STARTER_KITS,
  TEST_RUNNERS,
  type BlueprintConfig,
  type Choice,
  type Extra,
} from './blueprint-options';

/**
 * Round-trips BlueprintConfig ↔ URL query string.
 *
 * Design rules:
 * - Omit any value that matches the default → short, readable URLs.
 * - Extras are joined into a single `extra=a,b,c` param (one param, not repeated).
 * - Decoding validates every value against its allowed set; unknown values
 *   silently fall back to the default for that field.
 */

const SHORT_KEYS = {
  projectName: 'name',
  starterKit: 'kit',
  frontendStack: 'stack',
  database: 'db',
  cache: 'cache',
  queue: 'queue',
  testRunner: 'tests',
  extras: 'extra',
  dockerMode: 'docker',
  ciPreset: 'ci',
  gitMode: 'git',
} as const;

export function encodeConfig(config: BlueprintConfig): string {
  const params = new URLSearchParams();

  if (config.projectName.trim() && config.projectName !== DEFAULT_CONFIG.projectName) {
    params.set(SHORT_KEYS.projectName, config.projectName);
  }
  if (config.starterKit !== DEFAULT_CONFIG.starterKit) {
    params.set(SHORT_KEYS.starterKit, config.starterKit);
  }
  if (config.frontendStack !== DEFAULT_CONFIG.frontendStack) {
    params.set(SHORT_KEYS.frontendStack, config.frontendStack);
  }
  if (config.database !== DEFAULT_CONFIG.database) {
    params.set(SHORT_KEYS.database, config.database);
  }
  if (config.cache !== DEFAULT_CONFIG.cache) {
    params.set(SHORT_KEYS.cache, config.cache);
  }
  if (config.queue !== DEFAULT_CONFIG.queue) {
    params.set(SHORT_KEYS.queue, config.queue);
  }
  if (config.testRunner !== DEFAULT_CONFIG.testRunner) {
    params.set(SHORT_KEYS.testRunner, config.testRunner);
  }
  if (!sameExtras(config.extras, DEFAULT_CONFIG.extras)) {
    params.set(SHORT_KEYS.extras, config.extras.join(','));
  }
  if (config.dockerMode !== DEFAULT_CONFIG.dockerMode) {
    params.set(SHORT_KEYS.dockerMode, config.dockerMode);
  }
  if (config.ciPreset !== DEFAULT_CONFIG.ciPreset) {
    params.set(SHORT_KEYS.ciPreset, config.ciPreset);
  }
  if (config.gitMode !== DEFAULT_CONFIG.gitMode) {
    params.set(SHORT_KEYS.gitMode, config.gitMode);
  }

  return params.toString();
}

export function decodeConfig(search: string): BlueprintConfig {
  const params = new URLSearchParams(search);

  const rawName = params.get(SHORT_KEYS.projectName);
  const projectName = rawName?.trim() ? rawName.trim() : DEFAULT_CONFIG.projectName;

  return {
    projectName,
    starterKit: pickEnum(params.get(SHORT_KEYS.starterKit), STARTER_KITS, DEFAULT_CONFIG.starterKit),
    frontendStack: pickEnum(
      params.get(SHORT_KEYS.frontendStack),
      FRONTEND_STACKS,
      DEFAULT_CONFIG.frontendStack,
    ),
    database: pickEnum(params.get(SHORT_KEYS.database), DATABASES, DEFAULT_CONFIG.database),
    cache: pickEnum(params.get(SHORT_KEYS.cache), CACHES, DEFAULT_CONFIG.cache),
    queue: pickEnum(params.get(SHORT_KEYS.queue), QUEUES, DEFAULT_CONFIG.queue),
    testRunner: pickEnum(params.get(SHORT_KEYS.testRunner), TEST_RUNNERS, DEFAULT_CONFIG.testRunner),
    extras: parseExtras(params.get(SHORT_KEYS.extras)),
    dockerMode: pickEnum(params.get(SHORT_KEYS.dockerMode), DOCKER_MODES, DEFAULT_CONFIG.dockerMode),
    ciPreset: pickEnum(params.get(SHORT_KEYS.ciPreset), CI_PRESETS, DEFAULT_CONFIG.ciPreset),
    gitMode: pickEnum(params.get(SHORT_KEYS.gitMode), GIT_MODES, DEFAULT_CONFIG.gitMode),
  };
}

function pickEnum<T extends string>(
  raw: string | null,
  options: ReadonlyArray<Choice<T>>,
  fallback: T,
): T {
  if (raw === null) return fallback;
  return options.some((o) => o.value === raw) ? (raw as T) : fallback;
}

function parseExtras(raw: string | null): ReadonlyArray<Extra> {
  if (raw === null) return DEFAULT_CONFIG.extras;
  if (raw === '') return [];

  const allowed = new Set(EXTRAS.map((e) => e.value));
  const parsed = raw
    .split(',')
    .map((s) => s.trim())
    .filter((s): s is Extra => allowed.has(s as Extra));

  // De-duplicate while preserving order.
  return Array.from(new Set(parsed));
}

function sameExtras(a: ReadonlyArray<Extra>, b: ReadonlyArray<Extra>): boolean {
  if (a.length !== b.length) return false;
  const sortedA = [...a].sort();
  const sortedB = [...b].sort();
  return sortedA.every((v, i) => v === sortedB[i]);
}
