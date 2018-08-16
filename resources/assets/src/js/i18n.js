import Vue from 'vue';

/**
 * Translate according to given key.
 *
 * @param  {string} key
 * @param  {object} parameters
 * @return {string}
 */
export function trans(key, parameters = {}) {
    const segments = key.split('.');
    let temp = window.__bs_i18n__ || {};

    for (const i in segments) {
        if (!temp[segments[i]]) {
            return key;
        } else {
            temp = temp[segments[i]];
        }
    }

    for (const i in parameters) {
        if (parameters[i] !== undefined) {
            temp = temp.replace(':' + i, parameters[i]);
        }
    }

    return temp;
}

Vue.use(_Vue => {
    _Vue.prototype.$t = trans;
    _Vue.directive('t', (el, { value }) => {
        if (typeof value === 'string') {
            el.textContent = trans(value);
        } else if (typeof value === 'object') {
            el.textContent = trans(value.path, value.args);
        } else {
            if (process.env.NODE_ENV !== 'production') {
                console.warn('[i18n] Invalid arguments in `v-t` directive.');
            }
        }
    });
});

window.trans = trans;
