import type { BlueprintConfig, Extra, FrontendStack } from './blueprint-options';

export interface TreeNode {
  name: string;
  kind: 'dir' | 'file';
  /** Soft tag for highlighting why a node appears (which choice added it). */
  tag?: 'kit' | 'stack' | 'extra' | 'docker' | 'ci' | 'tests' | 'db';
  children?: TreeNode[];
}

const dir = (name: string, children: TreeNode[], tag?: TreeNode['tag']): TreeNode => ({
  name,
  kind: 'dir',
  ...(children.length > 0 ? { children } : {}),
  ...(tag ? { tag } : {}),
});

const file = (name: string, tag?: TreeNode['tag']): TreeNode => ({
  name,
  kind: 'file',
  ...(tag ? { tag } : {}),
});

/**
 * Build the directory preview tree from the current config.
 *
 * This is intentionally a *projection* — it reflects what the generated
 * project will roughly look like, not a byte-perfect snapshot. Aim for the
 * landmarks the user actually cares about (kit / stack / extras / docker / CI).
 */
export function buildTree(config: BlueprintConfig): TreeNode {
  return dir(config.projectName.trim() || 'my-app', [
    appDir(config),
    dir('bootstrap', [file('app.php'), dir('cache', [])]),
    configDir(config),
    databaseDir(config),
    dir('public', [file('index.php'), file('robots.txt')]),
    resourcesDir(config),
    routesDir(config),
    dir('storage', [dir('app', []), dir('framework', []), dir('logs', [])]),
    testsDir(config),
    ...dockerNodes(config),
    ...ciNodes(config),
    file('.env'),
    file('.env.example'),
    file('.gitignore'),
    file('artisan'),
    file('composer.json'),
    file('package.json'),
    ...(needsVite(config.frontendStack) ? [file('vite.config.js')] : []),
    ...extraRootFiles(config),
    file('README.md'),
  ]);
}

function appDir(config: BlueprintConfig): TreeNode {
  const httpControllers: TreeNode[] = [file('Controller.php')];
  if (config.starterKit !== 'none') httpControllers.push(file('Auth/AuthenticatedSessionController.php', 'kit'));

  const children: TreeNode[] = [
    dir('Http', [dir('Controllers', httpControllers), dir('Middleware', [file('TrustProxies.php')])]),
    dir('Models', [file('User.php')]),
    dir('Providers', [file('AppServiceProvider.php')]),
  ];

  if (config.starterKit === 'filament') {
    children.push(dir('Filament', [dir('Resources', [file('UserResource.php', 'kit')])], 'kit'));
  }
  if (config.extras.includes('horizon')) {
    children.push(dir('Console/Commands', [file('HorizonCommand.php', 'extra')]));
  }

  return dir('app', children);
}

function configDir(config: BlueprintConfig): TreeNode {
  const base = ['app.php', 'auth.php', 'cache.php', 'database.php', 'queue.php', 'session.php'];
  const extraConfigs: TreeNode[] = [];
  for (const e of config.extras) {
    const filename = configFileForExtra(e);
    if (filename) extraConfigs.push(file(filename, 'extra'));
  }
  return dir(
    'config',
    [...base.map((f) => file(f)), ...extraConfigs],
  );
}

function configFileForExtra(extra: Extra): string | null {
  switch (extra) {
    case 'horizon':
      return 'horizon.php';
    case 'telescope':
      return 'telescope.php';
    case 'pulse':
      return 'pulse.php';
    case 'octane':
      return 'octane.php';
    case 'scout':
      return 'scout.php';
    case 'sanctum':
      return 'sanctum.php';
    default:
      return null;
  }
}

function databaseDir(config: BlueprintConfig): TreeNode {
  const children: TreeNode[] = [
    dir('factories', [file('UserFactory.php')]),
    dir('migrations', [file('0001_01_01_000000_create_users_table.php')]),
    dir('seeders', [file('DatabaseSeeder.php')]),
  ];
  if (config.database === 'sqlite') children.push(file('database.sqlite', 'db'));
  return dir('database', children);
}

function resourcesDir(config: BlueprintConfig): TreeNode {
  const children: TreeNode[] = [dir('css', [file('app.css')]), dir('js', [file('app.js')]), dir('views', [file('welcome.blade.php')])];

  switch (config.frontendStack) {
    case 'inertia-vue':
      children[1] = dir(
        'js',
        [
          file('app.ts', 'stack'),
          dir('Pages', [file('Welcome.vue', 'stack'), file('Dashboard.vue', 'stack')], 'stack'),
          dir('Layouts', [file('AuthenticatedLayout.vue', 'stack')], 'stack'),
          dir('Components', [file('PrimaryButton.vue', 'stack')], 'stack'),
        ],
        'stack',
      );
      break;
    case 'inertia-react':
      children[1] = dir(
        'js',
        [
          file('app.tsx', 'stack'),
          dir('Pages', [file('Welcome.tsx', 'stack'), file('Dashboard.tsx', 'stack')], 'stack'),
          dir('Layouts', [file('AuthenticatedLayout.tsx', 'stack')], 'stack'),
          dir('Components', [file('PrimaryButton.tsx', 'stack')], 'stack'),
        ],
        'stack',
      );
      break;
    case 'livewire':
      children[2] = dir(
        'views',
        [
          file('welcome.blade.php'),
          dir('livewire', [file('counter.blade.php', 'stack')], 'stack'),
          dir('layouts', [file('app.blade.php', 'stack')], 'stack'),
        ],
        'stack',
      );
      break;
    case 'blade':
      if (config.starterKit !== 'none') {
        children[2] = dir(
          'views',
          [
            file('welcome.blade.php'),
            dir('auth', [file('login.blade.php', 'kit'), file('register.blade.php', 'kit')], 'kit'),
            file('dashboard.blade.php', 'kit'),
          ],
          'stack',
        );
      }
      break;
    case 'api':
    case 'none':
      // API-only / no frontend → keep minimal resources
      break;
  }

  return dir('resources', children);
}

function routesDir(config: BlueprintConfig): TreeNode {
  const children: TreeNode[] = [file('web.php'), file('console.php')];
  if (config.frontendStack === 'api' || config.extras.includes('sanctum')) {
    children.push(file('api.php', config.frontendStack === 'api' ? 'stack' : 'extra'));
  }
  if (config.starterKit !== 'none') children.push(file('auth.php', 'kit'));
  return dir('routes', children);
}

function testsDir(config: BlueprintConfig): TreeNode {
  const children: TreeNode[] = [
    dir('Feature', [file('ExampleTest.php')]),
    dir('Unit', [file('ExampleTest.php')]),
    file('TestCase.php'),
  ];
  if (config.testRunner === 'pest') children.unshift(file('Pest.php', 'tests'));
  if (config.extras.includes('dusk')) children.push(dir('Browser', [file('LoginTest.php', 'extra')], 'extra'));
  return dir('tests', children);
}

function dockerNodes(config: BlueprintConfig): TreeNode[] {
  const out: TreeNode[] = [];
  if (config.dockerMode === 'sail' || config.dockerMode === 'both') {
    out.push(file('docker-compose.yml', 'docker'));
  }
  if (config.dockerMode === 'production' || config.dockerMode === 'both') {
    out.push(file('Dockerfile', 'docker'));
    out.push(dir('docker', [file('php.ini', 'docker'), file('nginx.conf', 'docker')], 'docker'));
  }
  return out;
}

function ciNodes(config: BlueprintConfig): TreeNode[] {
  if (config.ciPreset === 'github-actions') {
    return [
      dir(
        '.github',
        [dir('workflows', [file('tests.yml', 'ci'), file('lint.yml', 'ci')], 'ci')],
        'ci',
      ),
    ];
  }
  return [];
}

function extraRootFiles(config: BlueprintConfig): TreeNode[] {
  const out: TreeNode[] = [];
  if (config.extras.includes('pint')) out.push(file('pint.json', 'extra'));
  if (config.extras.includes('larastan')) out.push(file('phpstan.neon', 'extra'));
  return out;
}

function needsVite(stack: FrontendStack): boolean {
  return stack !== 'none';
}
