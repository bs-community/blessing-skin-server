/* eslint-disable @typescript-eslint/no-unnecessary-condition */
const bus: { [name: string]: CallableFunction[] } = Object.create(null)

export function on(eventName: string, listener: CallableFunction) {
  (bus[eventName] || (bus[eventName] = [])).push(listener)
}

export function emit(eventName: string, payload?: any) {
  bus[eventName] && bus[eventName].forEach(listener => listener(payload))
}

blessing.event = { on, emit }
