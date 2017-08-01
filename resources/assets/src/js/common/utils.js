'use strict';

console.log(`\n %c Blessing Skin v${blessing.version} %c https://blessing.studio \n\n`, 'color: #fadfa3; background: #030307; padding:5px 0;', 'background: #fadfa3; padding:5px 0;');

/**
 * Check if given value is empty.
 *
 * @param  {any}  obj
 * @return {Boolean}
 */
function isEmpty(obj) {

    // null and undefined are "empty"
    if (obj == null) return true;

    if (typeof (obj) == 'number' || typeof (obj) == 'boolean') return false;

    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if (obj.length > 0)    return false;
    if (obj.length === 0)  return true;

    // If it isn't an object at this point
    // it is empty, but it can't be anything *but* empty
    // Is it empty?  Depends on your application.
    if (typeof obj !== 'object') return true;

    // Otherwise, does it have any properties of its own?
    // Note that this doesn't handle
    // toString and valueOf enumeration bugs in IE < 9
    for (var key in obj) {
        if (hasOwnProperty.call(obj, key)) return false;
    }

    return true;
}

/**
 * A fake fetch API. Returns Promises.
 *
 * @param  {array} option Same as options of jQuery.ajax()
 * @return {Promise}
 */
function fetch(option) {
    return Promise.resolve($.ajax(option));
}

/**
 * Get parameters in query string with key.
 *
 * @param  {string} key
 * @param  {string} defaultValue
 * @return {string}
 */
function getQueryString(key, defaultValue) {
    let result = location.search.match(new RegExp('[?&]'+key+'=([^&]+)','i'));

    if (result == null || result.length < 1){
        return defaultValue;
    } else {
        return result[1];
    }
}

/**
 * Return a debounced function
 *
 * @param {Function} func
 * @param {number}   delay
 * @param {Array}    args
 * @param {Object}   context
 */
function debounce(func, delay, args = [], context = undefined) {
    if (isNaN(delay) || typeof func !== 'function') {
        throw new Error('Arguments type of function "debounce" is incorrect!');
    }

    let timer = null;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            func.apply(context, args);
        }, delay);
    };
}

function url(relativeUri = '') {
    blessing.base_url = blessing.base_url || '';

    if (relativeUri[0] != '/') {
        relativeUri = '/' + relativeUri;
    }

    return blessing.base_url + relativeUri;
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        url,
        fetch,
        isEmpty,
        debounce,
        getQueryString,
    };
}
