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
  }
}

declare module 'vue/types/vue' {
  interface Vue {
    $t(key: string, parameters?: object): string
  }
}
