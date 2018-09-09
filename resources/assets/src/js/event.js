/** @type {{ [name: string]: Function[] }} */
const bus = Object.create(null);

/**
 * @param {string} eventName
 * @param {Function} listener
 */
export function on(eventName, listener) {
    (bus[eventName] || (bus[eventName] = [])).push(listener);
}

/**
 * @param {string} eventName
 * @param {any} payload
 */
export function emit(eventName, payload) {
    bus[eventName] && bus[eventName].forEach(listener => listener(payload));
}

blessing.event = { on, emit };
