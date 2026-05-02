import type { TreeNode } from '../../lib/tree-builder';
import './directory-tree.css';

interface DirectoryTreeProps {
  root: TreeNode;
}

export function DirectoryTree({ root }: DirectoryTreeProps) {
  return (
    <div className="directory-tree">
      <header className="directory-tree__header">
        <span className="directory-tree__title">project tree</span>
        <Legend />
      </header>
      <pre className="directory-tree__pre">
        <code>{renderNode(root, '', true, true)}</code>
      </pre>
    </div>
  );
}

function Legend() {
  const items: Array<{ tag: string; label: string }> = [
    { tag: 'kit', label: 'kit' },
    { tag: 'stack', label: 'stack' },
    { tag: 'extra', label: 'extra' },
    { tag: 'docker', label: 'docker' },
    { tag: 'ci', label: 'ci' },
  ];
  return (
    <ul className="directory-tree__legend">
      {items.map((it) => (
        <li key={it.tag} className={`directory-tree__legend-item directory-tree__tag--${it.tag}`}>
          {it.label}
        </li>
      ))}
    </ul>
  );
}

/**
 * Recursive ASCII renderer. Returns a flat array of styled spans plus
 * literal newlines so it copies cleanly out of the page.
 */
function renderNode(
  node: TreeNode,
  prefix: string,
  isLast: boolean,
  isRoot: boolean,
): React.ReactNode[] {
  const out: React.ReactNode[] = [];

  if (isRoot) {
    out.push(
      <span key="root" className={tokenClass(node)}>
        {node.name}
        {node.kind === 'dir' ? '/' : ''}
      </span>,
      '\n',
    );
  } else {
    const branch = isLast ? '└── ' : '├── ';
    out.push(
      <span key={`${prefix}-pre`} className="directory-tree__branch">
        {prefix + branch}
      </span>,
      <span key={`${prefix}-name`} className={tokenClass(node)}>
        {node.name}
        {node.kind === 'dir' ? '/' : ''}
      </span>,
      '\n',
    );
  }

  if (node.kind === 'dir' && node.children) {
    const children = node.children;
    const childPrefix = isRoot ? '' : prefix + (isLast ? '    ' : '│   ');
    children.forEach((child, i) => {
      const childIsLast = i === children.length - 1;
      out.push(
        <span key={`${prefix}-${i}`}>
          {renderNode(child, childPrefix, childIsLast, false)}
        </span>,
      );
    });
  }

  return out;
}

function tokenClass(node: TreeNode): string {
  const base = node.kind === 'dir' ? 'directory-tree__dir' : 'directory-tree__file';
  return node.tag ? `${base} directory-tree__tag--${node.tag}` : base;
}
