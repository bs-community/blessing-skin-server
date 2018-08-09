/* eslint-disable no-var */
import 'core-js/fn/array/includes';
import 'core-js/fn/array/find';
import 'es6-promise/auto';
import 'whatwg-fetch';

Number.parseInt = parseInt;

document.body.classList.replace = function (oldToken, newToken) {
    var list = document.body.classList;
    list.remove(oldToken);
    list.add(newToken);
};
