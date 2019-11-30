/* eslint-disable max-classes-per-file */
import 'jest-extended'
import Vue from 'vue'
import {
  Button,
  Input,
  Radio,
  Switch,
} from 'element-ui'

window.blessing = {
  base_url: '',
  site_name: 'Blessing Skin',
  version: '4.0.0',
  extra: {},
  i18n: {},
}

class Headers extends Map {
  constructor(headers: object = {}) {
    // @ts-ignore
    super(Object.entries(headers))
  }
}
class Request {
  public url: string

  public headers: Headers

  constructor(url: string, init: RequestInit) {
    this.url = url
    Object.assign(this, init)
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    this.headers = new Headers(Object.entries(init.headers || {}))
  }
}
Object.assign(window, { Headers, Request })

const noop = () => undefined
Object.assign(console, {
  log: noop,
  info: noop,
  warn: noop,
  error: noop,
})

Vue.prototype.$t = key => key

Vue.directive('t', (el: Element, { value }) => {
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
  put: jest.fn(),
  del: jest.fn(),
}

Vue.use(Button)
Vue.use(Input)
Vue.use(Radio)
Vue.use(Switch)
// @ts-ignore
Vue.prototype.$message = {
  info: jest.fn(),
  success: jest.fn(),
  warning: jest.fn(),
  error: jest.fn(),
}
