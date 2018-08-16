/**
 * @param {Function} func
 * @param {number} delay
 */
export function debounce(func, delay) {
    let timer;
    return () => {
        clearTimeout(timer);
        timer = setTimeout(func, delay);
    };
}

/**
 * Get parameters in query string with key.
 *
 * @param  {string} key
 * @param  {string} defaultValue
 * @return {string}
 */
export function queryString(key, defaultValue) {
    const result = location.search.match(new RegExp('[?&]' + key + '=([^&]+)', 'i'));

    if (result === null || result.length < 1) {
        return defaultValue;
    } else {
        return result[1];
    }
}
