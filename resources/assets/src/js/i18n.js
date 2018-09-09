import Vue from 'vue';

/**
 * Translate according to given key.
 *
 * @param  {string} key
 * @param  {object} parameters
 * @return {string}
 */
export function trans(key, parameters = Object.create(null)) {
    const segments = key.split('.');
    let temp = blessing.i18n || Object.create(null);

    for (const segment of segments) {
        if (!temp[segment]) {
            return key;
        } else {
            temp = temp[segment];
        }
    }

    Object.keys(parameters)
        .forEach(slot => temp = temp.replace(`:${slot}`, parameters[slot]));

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
            /* istanbul ignore next */
            if (process.env.NODE_ENV !== 'production') {
                console.warn('[i18n] Invalid arguments in `v-t` directive.');
            }
        }
    });
});

window.trans = trans;
