import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
import {resolve} from 'path';
import dts from 'vite-plugin-dts';

// Add build timestamp
const buildTimestamp = () => ({
  name: 'build-timestamp',
  generateBundle(_options: any, bundle: any) {
    const timestamp = new Date().toISOString();
    for (const fileName in bundle) {
      if (fileName.endsWith('.js')) {
        const chunk = bundle[fileName];
        if ('code' in chunk) {
          chunk.code = `/* Build: ${timestamp} */\n${chunk.code}`;
        }
      }
    }
  }
});

export default defineConfig({
  plugins: [
    react({
      jsxRuntime: 'classic',
      jsxImportSource: undefined
    }),
    dts({
      insertTypesEntry: true,
      include: ['src/index.ts', 'src/AmazonVariationsSimple.tsx', 'src/AmazonVariations.tsx', 'src/types/**/*', 'src/hooks/**/*'],
      exclude: [
        'src/**/*.test.*',
        'src/**/*.spec.*',
        'src/test/**/*',
        'src/components/**/*',  // Exclude problematic components
        'src/AmazonVariationsExample.tsx',  // Exclude example
        'src/main.tsx'  // Exclude development entry point
      ]
    }),
    buildTimestamp()
  ],
  // Development server configuration
  server: {
    port: 3000,
    open: true,
    host: true,
    cors: true
  },
  build: {
    lib: {
      entry: resolve(__dirname, 'src/bundle-with-globals.ts'),
      name: 'MagnalisterAmazonVariations',
      formats: ['umd'],  // Only UMD for standalone bundle
      fileName: () => `magnalister-amazon-variations-bundle.umd.js`
    },
    rollupOptions: {
      // Remove external dependencies - bundle React and ReactDOM with our component
      // external: ['react', 'react-dom'],  // Commented out to bundle everything
      output: {
        // No globals needed since we're bundling everything
        // globals: {
        //   'react': 'React',
        //   'react-dom': 'ReactDOM'
        // }
      }
    },
    sourcemap: true,
    minify: 'esbuild'  // Use esbuild instead of terser for CSP compatibility
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  },
  define: {
    __DEV__: JSON.stringify(process.env.NODE_ENV !== 'production'),
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
    'process.env': JSON.stringify({})
  }
});