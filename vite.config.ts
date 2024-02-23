import {defineConfig, splitVendorChunkPlugin} from 'vite';
import react from '@vitejs/plugin-react-swc';
import wasm from 'vite-plugin-wasm';
import topLevelAwait from 'vite-plugin-top-level-await';
import browserslistToEsbuild from 'browserslist-to-esbuild';

const root = new URL('resources/assets/src/', import.meta.url);

export default defineConfig({
	plugins: [
		react(),
		wasm(),
		topLevelAwait(),
		splitVendorChunkPlugin(),
	],
	resolve: {
		alias: {
			'@/': root.pathname,
			readline: new URL('scripts/cli/readline.ts', root).pathname,
			prompts: 'prompts/lib/index.js',
		},
	},
	publicDir: false,
	root: '',
	build: {
		target: browserslistToEsbuild(),
		sourcemap: true,
		copyPublicDir: false,
		outDir: new URL('public/app', import.meta.url).pathname,
		rollupOptions: {
			input: {
				app: '@/index.tsx',
				style: '@/styles/common.css',
			},
			treeshake: {
				preset: 'recommended',
			},
		},
	},
	base: '/app/',
});
