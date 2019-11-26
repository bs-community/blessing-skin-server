import Vue from 'vue'
import loadI18n from './scripts/i18n-text'
import './scripts/app'
import routes from './scripts/route'

Vue.config.productionTip = false

loadI18n().then(loadModules)

function loadModules() {
  const route = routes.find(
    // eslint-disable-next-line no-shadow
    route => new RegExp(`^${route.path}$`, 'i').test(blessing.route),
  )
  if (route) {
    if (route.module) {
      Promise.all(route.module.map(m => m()))
    }
    if (route.component) {
      Vue.prototype.$route = new RegExp(`^${route.path}$`, 'i').exec(blessing.route)
      // eslint-disable-next-line no-new
      new Vue({
        el: route.el,
        render: h => h(route.component),
      })
    }
  }
}
