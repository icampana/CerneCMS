import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'
import path from 'path'
import tailwindcss from '@tailwindcss/vite'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [svelte(), tailwindcss()],
  // Prevent Vite from copying the public folder (backend root) into the output asset folder
  publicDir: false,
  build: {
    manifest: true,
    outDir: 'public/assets',
    // Clear the output directory before building
    emptyOutDir: true,
    sourcemap: false,
    rollupOptions: {
      input: 'src/main.js',
      output: {
        manualChunks: (id) => {
          if (id.includes('node_modules')) {
             if (id.includes('@tiptap') || id.includes('prosemirror') || id.includes('svelte-tiptap')) {
                  return 'tiptap';
              }
              if (id.includes('flowbite')) {
                  return 'flowbite';
              }

              return 'vendor'; // all other package goes here
          }
        }
      }
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
