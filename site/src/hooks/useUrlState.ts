import { useEffect, useRef, useState } from 'react';
import { decodeConfig, encodeConfig } from '../lib/url-state';
import type { BlueprintConfig } from '../lib/blueprint-options';

/**
 * Two-way bind BlueprintConfig to the URL query string.
 *
 * - On mount, hydrates state from `window.location.search`.
 * - On every state change, writes the new query string with `replaceState`
 *   (not `pushState`) so each option toggle doesn't pollute browser history.
 * - On `popstate` (back/forward) re-reads the URL.
 */
export function useUrlState(): [BlueprintConfig, (next: BlueprintConfig) => void] {
  const [config, setConfig] = useState<BlueprintConfig>(() =>
    decodeConfig(typeof window === 'undefined' ? '' : window.location.search),
  );

  // Skip the very first effect: state already matches the URL.
  const isFirstWrite = useRef(true);

  useEffect(() => {
    if (isFirstWrite.current) {
      isFirstWrite.current = false;
      return;
    }
    const query = encodeConfig(config);
    const next = query ? `?${query}` : window.location.pathname;
    window.history.replaceState(null, '', next);
  }, [config]);

  useEffect(() => {
    function handlePop() {
      setConfig(decodeConfig(window.location.search));
    }
    window.addEventListener('popstate', handlePop);
    return () => window.removeEventListener('popstate', handlePop);
  }, []);

  return [config, setConfig];
}
