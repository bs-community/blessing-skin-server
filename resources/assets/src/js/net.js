import Vue from 'vue';
import { emit } from './event';
import { queryStringify } from './utils';
import { showAjaxError } from './notify';

const empty = Object.create(null);
/** @type Request */
export const init = {
    credentials: 'same-origin',
    headers: {
        'Accept': 'application/json',
    }
};

function retrieveToken() {
    const csrfField = document.querySelector('meta[name="csrf-token"]');
    return csrfField && csrfField.content;
}

/**
 * @param {Request} request
 */
export async function walkFetch(request) {
    request.headers.set('X-CSRF-TOKEN', retrieveToken());

    try {
        const response = await fetch(request);
        if (response.ok) {
            return response.headers.get('Content-Type') === 'application/json'
                ? response.json()
                : response.text();
        } else {
            const text = await response.text();
            emit('fetchError', text);
            showAjaxError(text);
        }
    } catch (error) {
        emit('fetchError', error);
        showAjaxError(error);
    }
}

export async function get(url, params = empty) {
    emit('beforeFetch', {
        method: 'GET',
        url,
        data: params
    });

    const qs = queryStringify(params);

    return walkFetch(new Request(`${blessing.base_url}${url}${qs && '?' + qs}`, init));
}

export async function post(url, data = empty) {
    emit('beforeFetch', {
        method: 'POST',
        url,
        data
    });

    const isFormData = data instanceof FormData;

    const request = new Request(`${blessing.base_url}${url}`, {
        body: isFormData ? data : JSON.stringify(data),
        method: 'POST',
        ...init
    });
    !isFormData && request.headers.set('Content-Type', 'application/json');

    return walkFetch(request);
}

Vue.use(_Vue => {
    _Vue.prototype.$http = {
        get,
        post,
    };
});

blessing.fetch = { get, post };
