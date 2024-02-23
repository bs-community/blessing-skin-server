import {emit} from './event';
import {showModal} from './notify';
import {t} from './i18n';

export type ResponseBody<T = undefined> = {
	code: number;
	message: string;
	data: T extends undefined ? never : T;
};

class HTTPError extends Error {
	response: Response;

	constructor(message: string, response: Response) {
		super(message);
		this.response = response;
	}
}

const empty: Record<string, never> = Object.create(null);
export const init: RequestInit = {
	credentials: 'same-origin',
	headers: new Headers({
		Accept: 'application/json',
	}),
};

function retrieveToken() {
	const csrfField = document.querySelector<HTMLMetaElement>(
		'meta[name="csrf-token"]',
	);

	return csrfField?.content || '';
}

export async function walkFetch(request: Request): Promise<any> {
	request.headers.set('X-CSRF-TOKEN', retrieveToken());

	try {
		const response = await fetch(request);
		const cloned = response.clone();
		const body
      = response.headers.get('Content-Type') === 'application/json'
      	? await response.json()
      	: await response.text();
		if (response.ok) {
			return body;
		}

		let {message} = body;

		if (response.status === 422) {
			// Process validation errors from Laravel.
			const {
				errors,
			}: {
				message: string;
				errors: Record<string, string[]>;
			} = body;
			return {
				code: 1,
				message: Object.keys(errors).map(field => errors[field][0])[0],
			};
		}

		if (response.status === 419) {
			return await showModal({
				mode: 'alert',
				text: t('general.csrf'),
			});
		}

		if (response.status === 403 || response.status === 400) {
			return await showModal({
				mode: 'alert',
				text: message,
				type: 'warning',
			});
		}

		if (body.exception && Array.isArray(body.trace)) {
			const trace = (body.trace as Array<{file: string; line: number}>)
				.map((t, i) => `[${i + 1}] ${t.file}#L${t.line}`)
				.join('<br>');
			message = `${message}<br><details>${trace}</details>`;
		}

		throw new HTTPError(message || String(body), cloned);
	} catch (error: any) {
		emit('fetchError', error);
		await showModal({
			mode: 'alert',
			title: t('general.fatalError'),
			dangerousHTML: error.message,
			type: 'danger',
			okButtonType: 'outline-light',
		});

		return {code: -1, message: t('general.fatalError')};
	}
}

export async function get<T = any>(url: string, parameters: Record<string, string> | URLSearchParams = empty): Promise<T> {
	emit('beforeFetch', {
		method: 'GET',
		url,
		data: parameters,
	});

	const qs = new URLSearchParams(parameters).toString();

	return walkFetch(new Request(`${blessing.base_url}${url}?${qs}`, init));
}

async function nonGet<T = any>(
	method: string,
	url: string,
	data?: FormData | Record<string, unknown>,
): Promise<T> {
	emit('beforeFetch', {
		method: method.toUpperCase(),
		url,
		data,
	});

	const request = new Request(`${blessing.base_url}${url}`, {
		body: data instanceof FormData ? data : JSON.stringify(data),
		method: method.toUpperCase(),
		...init,
	});
	if (!(data instanceof FormData)) {
		request.headers.set('Content-Type', 'application/json');
	}

	return walkFetch(request);
}

export async function post<T = any>(
	url: string,
	data?: FormData | Record<string, unknown>,
): Promise<T> {
	return nonGet<T>('POST', url, data);
}

export async function put<T = any>(
	url: string,
	data?: FormData | Record<string, unknown>,
): Promise<T> {
	return nonGet<T>('PUT', url, data);
}

export async function del<T = any>(
	url: string,
	data?: FormData | Record<string, unknown>,
): Promise<T> {
	return nonGet<T>('DELETE', url, data);
}

blessing.fetch = {
	get,
	post,
	put,
	del,
};
