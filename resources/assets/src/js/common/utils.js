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
 * Check if the `resize` event is fired by scrolling on a mobile browser
 * whose address bar (e.g. Chrome) will hide automatically when scrolling.
 *
 * @return {Boolean}
 */
function isMobileBrowserScrolling() {
    let currentWindowWidth  = $(window).width();
    let currentWindowHeight = $(window).height();

    if ($.cachedWindowWidth === undefined) {
        $.cachedWindowWidth = currentWindowWidth;
    }

    if ($.cachedWindowHeight === undefined) {
        $.cachedWindowHeight = currentWindowHeight;
    }

    let isWidthChanged  = (currentWindowWidth  !== $.cachedWindowWidth);
    let isHeightChanged = (currentWindowHeight !== $.cachedWindowHeight);

    // If the window width & height changes simultaneously, the resize can't be fired by scrolling.
    if (isWidthChanged && isHeightChanged) {
        return false;
    }

    // If only width was changed, it also can't be.
    if (isWidthChanged) {
        return false;
    }

    // If width didn't change but height changed ?
    if (isHeightChanged) {
        let last = $.lastWindowHeight;
        $.lastWindowHeight = currentWindowHeight;

        if (last === undefined || currentWindowHeight == last) {
            return true;
        }
    }

    // If both width & height did not change
    return false;
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

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        url,
        fetch,
        isEmpty,
        debounce,
        getQueryString,
        isMobileBrowserScrolling,
    };
}
