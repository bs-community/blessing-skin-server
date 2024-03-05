import {defineConfig, splitVendorChunkPlugin} from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react-swc';
import wasm from 'vite-plugin-wasm';
import topLevelAwait from 'vite-plugin-top-level-await';
import browserslistToEsbuild from 'browserslist-to-esbuild';

const root = new URL('resources/assets/src/', import.meta.url);

export default defineConfig({
	plugins: [
		laravel([
			new URL('index.tsx', root).pathname,
			new URL('scripts/homePage.ts', root).pathname,
			new URL('app.css', root).pathname,
			new URL('spectre.css', root).pathname,
			new URL('home.css', root).pathname,
		]),
		react(),
		wasm(),
		topLevelAwait(),
		splitVendorChunkPlugin(),
	],
	resolve: {
		alias: {
			'@/': root.pathname,
			readline: new URL('scripts/cli/readline.ts', root).pathname,
			'~bootstrap': new URL('node_modules/admin-lte/node_modules/bootstrap', import.meta.url).pathname,
			'spectre.css': new URL('node_modules/spectre.css', import.meta.url).pathname,
		},
	},
	publicDir: false,
	build: {
		target: browserslistToEsbuild(),
		sourcemap: true,
		copyPublicDir: false,
		rollupOptions: {
			treeshake: {
				preset: 'recommended',
			},
		},
	},
});
