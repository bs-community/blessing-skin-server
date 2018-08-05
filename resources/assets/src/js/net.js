import Vue from 'vue';
import axios from 'axios';
import { showAjaxError } from './notify';

axios.defaults.baseURL = blessing.base_url;
axios.defaults.validateStatus = status => (status >= 200 && status < 300) || status === 422;

axios.interceptors.response.use(
    response => response,
    showAjaxError
);

const empty = Object.create(null);
const init = {
    credentials: 'same-origin',
    headers: {
        'Content-Type': 'application/json'
    }
};

async function walkFetch(request) {
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
    const qs = Object
        .keys(params)
        .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
        .join('&');

    return walkFetch(new Request(`${blessing.base_url}${url}${qs && '?' + qs}`, init));
}

export async function post(url, data = empty) {
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
