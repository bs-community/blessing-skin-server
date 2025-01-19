import fs from 'node:fs';
import yaml from 'js-yaml';
import '@testing-library/jest-dom/vitest';

window.blessing = {
	base_url: '',
	locale: 'en',
	site_name: 'Blessing Skin',
	version: '4.0.0',
	extra: {},
	i18n: yaml.load(fs.readFileSync('resources/lang/en/front-end.yml', 'utf8')) as Record<string, unknown>,
};

class Headers extends Map {
	constructor(headers: Record<string, unknown> = {}) {
		// @ts-ignore
		super(Object.entries(headers));
	}
}
class Request {
	public url: string;

	public headers: Headers;

	constructor(url: string, init: RequestInit) {
		this.url = url;
		Object.assign(this, init);
		// eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
		this.headers = new Headers(Object.entries(init.headers || {}));
	}
}
Object.assign(window, {Headers, Request});

const noop = () => undefined;
Object.assign(console, {
	warn: noop,
	error: noop,
});
