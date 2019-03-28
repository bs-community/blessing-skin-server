import Vue from 'vue'
import './js'
import * as emitter from './js/event'
import routes from './route'

Vue.config.productionTip = false

loadI18n().then(loadModules)

async function loadI18n() {
  const langs = [
    { lang: 'en', load: () => import('../../lang/en/front-end.yml') },
    { lang: 'zh_CN', load: () => import('../../lang/zh_CN/front-end.yml') },
  ]
  const texts = await langs.find(({ lang }) => lang === blessing.locale).load()
  blessing.i18n = Object.assign(blessing.i18n || Object.create(null), texts)
}

function loadModules() {
  const route = routes.find(
    // eslint-disable-next-line no-shadow
    route => (new RegExp(`^${route.path}$`, 'i')).test(blessing.route)
  )
  if (route) {
    if (route.module) {
      Promise.all(route.module.map(m => m()))
    }
    if (route.component) {
      Vue.prototype.$route = (new RegExp(`^${route.path}$`, 'i')).exec(blessing.route)
      // eslint-disable-next-line no-new
      new Vue({
        el: route.el,
        mounted() {
          setTimeout(() => emitter.emit('mounted', { el: route.el }), 100)
        },
        render: h => h(route.component),
      })
    }
  }
}
