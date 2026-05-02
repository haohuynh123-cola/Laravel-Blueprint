import { useMemo } from 'react';
import type { BlueprintConfig } from '../../lib/blueprint-options';
import { buildCommand } from '../../lib/command-builder';
import { buildTree } from '../../lib/tree-builder';
import { CommandBlock } from './CommandBlock';
import { DirectoryTree } from './DirectoryTree';
import './preview-panel.css';

interface PreviewPanelProps {
  config: BlueprintConfig;
}

export function PreviewPanel({ config }: PreviewPanelProps) {
  const command = useMemo(() => buildCommand(config), [config]);
  const tree = useMemo(() => buildTree(config), [config]);

  return (
    <aside className="preview-panel" aria-labelledby="preview-heading">
      <h2 id="preview-heading" className="preview-panel__heading">
        Paste this into your terminal
      </h2>
      <p className="preview-panel__lede">
        Run it once, get a runnable Laravel project with everything wired up.
      </p>

      <CommandBlock command={command} />
      <DirectoryTree root={tree} />
    </aside>
  );
}
