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
        'Content-Type': 'application/json',
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
            return response.json();
        } else {
            showAjaxError(await response.text());
        }
    } catch (error) {
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

    return walkFetch(new Request(`${blessing.base_url}${url}`, {
        body: JSON.stringify(data),
        method: 'POST',
        ...init
    }));
}

Vue.use(_Vue => {
    _Vue.prototype.$http = {
        get,
        post,
    };
});

window.bsAjax = { get, post };
