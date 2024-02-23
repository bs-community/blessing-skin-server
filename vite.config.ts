import {defineConfig, splitVendorChunkPlugin} from 'vite';
import react from '@vitejs/plugin-react-swc';
import wasm from 'vite-plugin-wasm';
import browserslistToEsbuild from 'browserslist-to-esbuild';

export default defineConfig({
	plugins: [
		react(),
		wasm(),
		splitVendorChunkPlugin(),
	],
	resolve: {
		alias: {
			'@/': new URL('resources/assets/src/', import.meta.url).pathname,
			readline: '@/scripts/cli/readline.ts',
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
