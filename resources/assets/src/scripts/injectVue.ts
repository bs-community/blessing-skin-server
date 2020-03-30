import type { VueConstructor } from 'vue'
import { t } from './i18n'
import * as fetch from './net'

export default function (Vue: VueConstructor) {
  injectI18n(Vue)
  injectFetch(Vue)
}

function injectI18n(Vue: VueConstructor) {
  Vue.use((_Vue) => {
    // eslint-disable-next-line @typescript-eslint/unbound-method
    _Vue.prototype.$t = t
    _Vue.directive('t', (el, { value }) => {
      if (typeof value === 'string') {
        el.textContent = t(value)
      } else if (typeof value === 'object') {
        el.textContent = t(value.path, value.args)
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
}

function injectFetch(Vue: VueConstructor) {
  Vue.use((_Vue) => {
    Object.defineProperty(_Vue.prototype, '$http', {
      get: () => ({
        get: fetch.get,
        post: fetch.post,
        put: fetch.put,
        del: fetch.del,
      }),
    })
  })
}
