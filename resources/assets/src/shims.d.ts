import Vue from 'vue'

declare global {
  var blessing: {
    base_url: string
    debug: boolean
    env: string
    fallback_locale: string
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
    }

    event: {
      on(eventName: string, listener: Function): void
      emit(eventName: string, payload: object): void
    }

    notify: {
      showModal(
        message: string,
        title?: string,
        type?: string,
        options?: Partial<{ btnText: string, callback: string, destroyOnClose: boolean }>
      )
    }
  }
}

declare module 'vue/types/vue' {
  interface Vue {
    $t(key: string, parameters?: object): string

    $http: {
      get(url: string, params?: object)

      post(url: string, data?: object): { errno?: number, msg?: string }
    }

    $route: string[]
  }
}
