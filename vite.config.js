import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [svelte()],
  build: {
    manifest: true,
    outDir: 'public/assets',
    // Clear the output directory before building
    emptyOutDir: true,
    rollupOptions: {
      input: 'src/main.js'
    }
  },
  server: {
    // Enable CORS for development
    cors: true,
    origin: 'http://localhost:5173'
  },
  resolve: {
    alias: {
      $lib: path.resolve('./src/lib')
    }
  }
})
