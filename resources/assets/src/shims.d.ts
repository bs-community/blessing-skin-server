import JQuery from 'jquery'
import { ModalOptions, ModalResult } from './components/Modal'
import { Toast } from './scripts/toast'

declare global {
  // eslint-disable-next-line no-redeclare
  let blessing: {
    base_url: string
    debug: boolean
    env: string
    locale: string
    site_name: string
    timezone: string
    version: string
    route: string
    extra: any
    i18n: object

    fetch: {
      get(url: string, params?: object): Promise<object>
      post(url: string, data?: object): Promise<object>
      put(url: string, data?: object): Promise<object>
      del(url: string, data?: object): Promise<object>
    }

    event: {
      on(eventName: string, listener: Function): void
      emit(eventName: string, payload: object): void
    }

    notify: {
      showModal(options?: ModalOptions): Promise<ModalResult>
      toast: Toast
    }
  }
}
