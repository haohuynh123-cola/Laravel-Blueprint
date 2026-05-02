import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// `base` is set for GitHub Pages deployment under /Laravel-Blueprint/.
// Override with VITE_BASE=/ when deploying to a custom domain or other host.
export default defineConfig({
  plugins: [react()],
  base: process.env.VITE_BASE ?? '/Laravel-Blueprint/',
});
