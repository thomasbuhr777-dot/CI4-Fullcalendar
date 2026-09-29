import { defineConfig } from 'vite';

export default defineConfig({
  base: '/build/',
  publicDir: false,
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: 'resources/js/app.js',
        calendar: 'resources/js/calendar.js',
      },
    },
  },
});
