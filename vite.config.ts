import type {GetModuleInfo} from 'rollup';
import {execSync} from 'node:child_process';
import {env} from 'node:process';
import react from '@vitejs/plugin-react-swc';
import browserslistToEsbuild from 'browserslist-to-esbuild';
import laravel from 'laravel-vite-plugin';
import {defineConfig} from 'vite';

// eslint-disable-next-line ts/naming-convention, regexp/no-unused-capturing-group
const CSS_LANGS_RE = /\.(css|less|sass|scss|styl|stylus|pcss|postcss|sss)(?:$|\?)/;
// eslint-disable-next-line ts/naming-convention
export const isCSSRequest = (request: string): boolean =>
	CSS_LANGS_RE.test(request);

class SplitVendorChunkCache {
	cache: Map<string, boolean>;
	constructor() {
		this.cache = new Map<string, boolean>();
	}

	reset(): void {
		this.cache = new Map<string, boolean>();
	}
}

function staticImportedByEntry(
	id: string,
	getModuleInfo: GetModuleInfo,
	cache: Map<string, boolean>,
	importStack: string[] = [],
): boolean {
	if (cache.has(id)) {
		return cache.get(id)!;
	}

	if (importStack.includes(id)) {
		// Circular deps!
		cache.set(id, false);
		return false;
	}

	const module_ = getModuleInfo(id);
	if (!module_) {
		cache.set(id, false);
		return false;
	}

	if (module_.isEntry) {
		cache.set(id, true);
		return true;
	}

	const someImporterIs = module_.importers.some(importer =>
		staticImportedByEntry(
			importer,
			getModuleInfo,
			cache,
			importStack.concat(id),
		));
	cache.set(id, someImporterIs);
	return someImporterIs;
}

const chunkCache = new SplitVendorChunkCache();
const root = new URL('resources/assets/src/', import.meta.url);

export default defineConfig({
	/* Disabled
	base: env.NODE_ENV === 'production'
		? '{{ cdn_base }}/app/'
		: 'GITPOD_REPO_ROOT' in env
			? '//localhost:8080/app/'
			: `${execSync('gp url 8080').toString()}/app/`,
			*/
	plugins: [
		laravel([
			new URL('index.tsx', root).pathname,
			new URL('scripts/homePage.ts', root).pathname,
			new URL('app.css', root).pathname,
			new URL('spectre.css', root).pathname,
			new URL('home.css', root).pathname,
		]),
		react({jsxImportSource: '@emotion/react'}),
	],
	resolve: {
		alias: {
			/* eslint-disable ts/naming-convention */
			'@/': root.pathname,
			'~bootstrap': new URL('node_modules/admin-lte/node_modules/bootstrap', import.meta.url).pathname,
			'spectre.css': new URL('node_modules/spectre.css', import.meta.url).pathname,
			/* eslint-enable ts/naming-convention */
		},
	},
	publicDir: false,
	/* Disabled
	experimental: {
		renderBuiltUrl(filename, {hostType}) {
			if (hostType === 'js') {
				return {runtime: `window.__toCdnUrl(${JSON.stringify(filename)})`};
			}

			return {relative: true};
		},
	},
	*/
	build: {
		target: browserslistToEsbuild(),
		sourcemap: true,
		copyPublicDir: false,
		rollupOptions: {
			treeshake: {
				preset: 'recommended',
			},
			output: {
				manualChunks(path, _meta) {
					if (path.includes('node_modules')
						&& !isCSSRequest(path)
						&& staticImportedByEntry(path, _meta.getModuleInfo, chunkCache.cache)) {
						return 'vendor';
					}
				},
			},
		},
	},
});
