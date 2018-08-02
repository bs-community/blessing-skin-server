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
