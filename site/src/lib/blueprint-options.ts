/**
 * Single source of truth for every option the CLI accepts.
 * Mirrors src/Config/*.php in the PHP package — keep in sync.
 */

export type StarterKit = 'none' | 'breeze' | 'jetstream' | 'filament';
export type FrontendStack =
  | 'blade'
  | 'livewire'
  | 'inertia-vue'
  | 'inertia-react'
  | 'api'
  | 'none';
export type Database = 'mysql' | 'pgsql' | 'sqlite' | 'mariadb';
export type TestRunner = 'pest' | 'phpunit';
export type Extra =
  | 'horizon'
  | 'telescope'
  | 'pulse'
  | 'octane'
  | 'scout'
  | 'sanctum'
  | 'pint'
  | 'larastan'
  | 'dusk'
  | 'sail';
export type DockerMode = 'none' | 'sail' | 'production' | 'both';
export type CiPreset = 'none' | 'github-actions';
export type GitMode = 'skip' | 'init' | 'commit';

export interface BlueprintConfig {
  projectName: string;
  starterKit: StarterKit;
  frontendStack: FrontendStack;
  database: Database;
  testRunner: TestRunner;
  extras: ReadonlyArray<Extra>;
  dockerMode: DockerMode;
  ciPreset: CiPreset;
  gitMode: GitMode;
}

export interface Choice<T extends string> {
  value: T;
  label: string;
  description?: string;
}

export const STARTER_KITS: ReadonlyArray<Choice<StarterKit>> = [
  { value: 'none', label: 'None', description: 'Bare Laravel, no auth scaffolding' },
  { value: 'breeze', label: 'Breeze', description: 'Minimal auth, your choice of stack' },
  { value: 'jetstream', label: 'Jetstream', description: 'Teams, 2FA, profile management' },
  { value: 'filament', label: 'Filament', description: 'Admin panel out of the box' },
];

export const FRONTEND_STACKS: ReadonlyArray<Choice<FrontendStack>> = [
  { value: 'blade', label: 'Blade + Alpine' },
  { value: 'livewire', label: 'Livewire' },
  { value: 'inertia-vue', label: 'Inertia + Vue' },
  { value: 'inertia-react', label: 'Inertia + React' },
  { value: 'api', label: 'API only' },
  { value: 'none', label: 'None' },
];

export const DATABASES: ReadonlyArray<Choice<Database>> = [
  { value: 'sqlite', label: 'SQLite', description: 'Zero setup' },
  { value: 'mysql', label: 'MySQL' },
  { value: 'pgsql', label: 'PostgreSQL' },
  { value: 'mariadb', label: 'MariaDB' },
];

export const TEST_RUNNERS: ReadonlyArray<Choice<TestRunner>> = [
  { value: 'pest', label: 'Pest' },
  { value: 'phpunit', label: 'PHPUnit' },
];

export const EXTRAS: ReadonlyArray<Choice<Extra>> = [
  { value: 'pint', label: 'Pint', description: 'Opinionated PHP code style' },
  { value: 'larastan', label: 'Larastan', description: 'PHPStan for Laravel' },
  { value: 'horizon', label: 'Horizon', description: 'Redis queue dashboard' },
  { value: 'telescope', label: 'Telescope', description: 'Request / query debugger' },
  { value: 'pulse', label: 'Pulse', description: 'Performance dashboard' },
  { value: 'octane', label: 'Octane', description: 'High-performance app server' },
  { value: 'scout', label: 'Scout', description: 'Full-text search' },
  { value: 'sanctum', label: 'Sanctum', description: 'API tokens / SPA auth' },
  { value: 'dusk', label: 'Dusk', description: 'Browser tests' },
  { value: 'sail', label: 'Sail', description: 'Docker dev environment' },
];

export const DOCKER_MODES: ReadonlyArray<Choice<DockerMode>> = [
  { value: 'none', label: 'None' },
  { value: 'sail', label: 'Sail (dev)' },
  { value: 'production', label: 'Production Dockerfile' },
  { value: 'both', label: 'Sail + production' },
];

export const CI_PRESETS: ReadonlyArray<Choice<CiPreset>> = [
  { value: 'none', label: 'None' },
  { value: 'github-actions', label: 'GitHub Actions' },
];

export const GIT_MODES: ReadonlyArray<Choice<GitMode>> = [
  { value: 'skip', label: 'Skip' },
  { value: 'init', label: 'git init' },
  { value: 'commit', label: 'git init + commit' },
];

export const DEFAULT_CONFIG: BlueprintConfig = {
  projectName: 'my-app',
  starterKit: 'breeze',
  frontendStack: 'inertia-vue',
  database: 'pgsql',
  testRunner: 'pest',
  extras: ['pint', 'larastan'],
  dockerMode: 'production',
  ciPreset: 'github-actions',
  gitMode: 'commit',
};

/**
 * Some starter kits constrain which frontend stacks are valid.
 * Keeps the UI from offering an Inertia stack with Filament, etc.
 */
export function allowedFrontendStacks(kit: StarterKit): ReadonlyArray<FrontendStack> {
  switch (kit) {
    case 'breeze':
      return ['blade', 'livewire', 'inertia-vue', 'inertia-react', 'api'];
    case 'jetstream':
      return ['livewire', 'inertia-vue'];
    case 'filament':
      return ['blade'];
    case 'none':
      return ['none'];
  }
}
