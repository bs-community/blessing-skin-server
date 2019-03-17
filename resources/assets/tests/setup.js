/* eslint-disable import/no-extraneous-dependencies */
import 'jest-extended'
import Vue from 'vue'

window.blessing = {
  base_url: '',
  site_name: 'Blessing Skin',
  version: '4.0.0',
}

window.Headers = class extends Map {
  constructor(headers = {}) {
    super(Object.entries(headers))
  }
}

const noop = () => undefined
// eslint-disable-next-line no-console
Object.keys(console).forEach(method => (console[method] = noop))

Vue.prototype.$t = key => key

Vue.directive('t', (el, { value }) => {
  if (typeof value === 'string') {
    el.innerHTML = value
  } else if (typeof value === 'object') {
    el.innerHTML = value.path
  } else {
    throw new Error('[i18n] Invalid arguments in `v-t` directive.')
  }
})

Vue.prototype.$http = {
  get: jest.fn(),
  post: jest.fn(),
}
