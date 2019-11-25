import Vue from 'vue'

export function trans(key: string, parameters = Object.create(null)): string {
  const segments = key.split('.')
  let temp = (
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    blessing.i18n || Object.create(null)
  ) as { [k: string]: string | { [k: string]: string } }
  let result = ''

  for (const segment of segments) {
    if (!temp[segment]) {
      return key
    }
    const middle = temp[segment]
    if (typeof middle === 'string') {
      result = middle
    } else {
      temp = middle
    }
  }

  Object.keys(parameters)
    .forEach(slot => (result = result.replace(`:${slot}`, parameters[slot])))

  return result
}

Vue.use(_Vue => {
  // eslint-disable-next-line @typescript-eslint/unbound-method
  _Vue.prototype.$t = trans
  _Vue.directive('t', (el, { value }) => {
    if (typeof value === 'string') {
      el.textContent = trans(value)
    } else if (typeof value === 'object') {
      el.textContent = trans(value.path, value.args)
    } else {
      /* istanbul ignore next */
      // eslint-disable-next-line no-lonely-if
      if (process.env.NODE_ENV !== 'production') {
        // eslint-disable-next-line no-console
        console.warn('[i18n] Invalid arguments in `v-t` directive.')
      }
    }
  })
})

// eslint-disable-next-line @typescript-eslint/unbound-method
window.trans = trans
