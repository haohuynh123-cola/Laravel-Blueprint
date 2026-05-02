import { useState } from 'react';
import type { RenderedCommand } from '../../lib/command-builder';
import './command-block.css';

interface CommandBlockProps {
  command: RenderedCommand;
}

type Toast = 'copied' | 'shared' | null;

export function CommandBlock({ command }: CommandBlockProps) {
  const [toast, setToast] = useState<Toast>(null);

  const flashToast = (kind: Exclude<Toast, null>) => {
    setToast(kind);
    window.setTimeout(() => setToast(null), 1500);
  };

  const copyCommand = async () => {
    if (await writeClipboard(command.plain)) flashToast('copied');
  };

  const copyShareLink = async () => {
    if (await writeClipboard(window.location.href)) flashToast('shared');
  };

  return (
    <div className="command-block">
      <div className="command-block__chrome">
        <span className="command-block__dot" data-color="red" />
        <span className="command-block__dot" data-color="amber" />
        <span className="command-block__dot" data-color="green" />
        <span className="command-block__title">terminal</span>
        <button
          type="button"
          className="command-block__action"
          onClick={copyShareLink}
          aria-label="Copy link to this configuration"
        >
          {toast === 'shared' ? 'link copied' : 'share'}
        </button>
        <button
          type="button"
          className="command-block__action"
          onClick={copyCommand}
          aria-label="Copy command"
        >
          {toast === 'copied' ? 'copied' : 'copy'}
        </button>
      </div>

      <pre className="command-block__pre">
        <code>
          {command.tokens.map((line, lineIdx) => (
            <span key={lineIdx} className="command-block__line">
              {lineIdx === 0 && <span className="command-block__prompt">$ </span>}
              {line.map((token, tokenIdx) => (
                <span
                  key={tokenIdx}
                  className={`command-block__token command-block__token--${token.kind}`}
                >
                  {token.text}
                </span>
              ))}
              {'\n'}
            </span>
          ))}
        </code>
      </pre>
    </div>
  );
}

/**
 * Returns true on success, false if the Clipboard API is unavailable
 * (insecure context, denied permission, ancient browser). Caller decides
 * whether to surface a toast.
 */
async function writeClipboard(text: string): Promise<boolean> {
  if (!navigator.clipboard) return false;
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch {
    return false;
  }
}
