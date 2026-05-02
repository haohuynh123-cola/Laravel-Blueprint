import type { BlueprintConfig } from './blueprint-options';
import { DEFAULT_CONFIG } from './blueprint-options';

interface Token {
  kind: 'cmd' | 'arg' | 'flag' | 'value' | 'continuation';
  text: string;
}

export interface RenderedCommand {
  /** Plain text — what the copy button puts on the clipboard. */
  plain: string;
  /** Tokenized for syntax highlighting in the terminal panel. */
  tokens: ReadonlyArray<ReadonlyArray<Token>>;
}

/**
 * Builds the `blueprint new …` invocation matching the current config.
 * Omits flags whose value matches the CLI default — cleaner output.
 */
export function buildCommand(config: BlueprintConfig): RenderedCommand {
  const flags: Array<[string, string]> = [];

  if (config.starterKit !== DEFAULT_CONFIG.starterKit) flags.push(['kit', config.starterKit]);
  if (config.frontendStack !== DEFAULT_CONFIG.frontendStack) {
    flags.push(['stack', config.frontendStack]);
  }
  if (config.database !== DEFAULT_CONFIG.database) flags.push(['database', config.database]);
  if (config.testRunner !== DEFAULT_CONFIG.testRunner) flags.push(['tests', config.testRunner]);

  for (const extra of config.extras) flags.push(['extra', extra]);

  if (config.dockerMode !== DEFAULT_CONFIG.dockerMode) flags.push(['docker', config.dockerMode]);
  if (config.ciPreset !== DEFAULT_CONFIG.ciPreset) flags.push(['ci', config.ciPreset]);
  if (config.gitMode !== DEFAULT_CONFIG.gitMode) flags.push(['git', config.gitMode]);

  const projectName = config.projectName.trim() || 'my-app';

  // Single-line plain output for clipboard, multi-line tokenized for display.
  const plainParts = ['blueprint', 'new', projectName];
  for (const [k, v] of flags) plainParts.push(`--${k}=${v}`);
  const plain = plainParts.join(' ');

  const lines: Token[][] = [];
  lines.push([
    { kind: 'cmd', text: 'blueprint' },
    { kind: 'arg', text: ' new' },
    { kind: 'arg', text: ` ${projectName}` },
    ...(flags.length > 0 ? [{ kind: 'continuation' as const, text: ' \\' }] : []),
  ]);

  flags.forEach(([k, v], i) => {
    const isLast = i === flags.length - 1;
    const indent = '  ';
    const line: Token[] = [
      { kind: 'flag', text: `${indent}--${k}` },
      { kind: 'arg', text: '=' },
      { kind: 'value', text: v },
    ];
    if (!isLast) line.push({ kind: 'continuation', text: ' \\' });
    lines.push(line);
  });

  return { plain, tokens: lines };
}
