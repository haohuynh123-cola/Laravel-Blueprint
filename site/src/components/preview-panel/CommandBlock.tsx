import { useState } from 'react';
import type { RenderedCommand } from '../../lib/command-builder';
import './command-block.css';

interface CommandBlockProps {
  command: RenderedCommand;
}

export function CommandBlock({ command }: CommandBlockProps) {
  const [copied, setCopied] = useState(false);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(command.plain);
      setCopied(true);
      window.setTimeout(() => setCopied(false), 1500);
    } catch {
      // Clipboard API blocked (e.g. insecure context) — fail quietly,
      // the user can still triple-click and copy manually.
    }
  };

  return (
    <div className="command-block">
      <div className="command-block__chrome">
        <span className="command-block__dot" data-color="red" />
        <span className="command-block__dot" data-color="amber" />
        <span className="command-block__dot" data-color="green" />
        <span className="command-block__title">terminal</span>
        <button type="button" className="command-block__copy" onClick={copy}>
          {copied ? 'copied' : 'copy'}
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
