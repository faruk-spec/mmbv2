import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: resolve(__dirname, '../../public/assets/js/support'),
    emptyOutDir: true,
    rollupOptions: {
      input: {
        wizard:  resolve(__dirname, 'src/main-wizard.jsx'),
        builder: resolve(__dirname, 'src/main-builder.jsx'),
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: '[name].[ext]',
      },
    },
  },
});
