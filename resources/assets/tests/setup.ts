import fs from 'fs'
import '@testing-library/jest-dom/vitest'
import yaml from 'js-yaml'


window.blessing = {
  base_url: '',
  locale: 'en',
  site_name: 'Blessing Skin',
  version: '4.0.0',
  extra: {},
  i18n: yaml.load(fs.readFileSync('resources/lang/en/front-end.yml', 'utf8')) as object,
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
Object.assign(window, {Headers, Request})

const noop = () => undefined
Object.assign(console, {
  warn: noop,
  error: noop,
})
