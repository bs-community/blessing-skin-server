import 'jest-extended';
import Vue from 'vue';

window.blessing = {
    base_url: ''
};

console.log = console.warn = console.error = () => {};

Vue.prototype.$t = key => key;

Vue.directive('t', (el, { value }) => {
    if (typeof value === 'string') {
        el.innerHTML = value;
    } else if (typeof value === 'object') {
        el.innerHTML = value.path;
    } else {
        throw new Error('[i18n] Invalid arguments in `v-t` directive.');
    }
});
