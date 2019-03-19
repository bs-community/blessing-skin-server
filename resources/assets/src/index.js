import Vue from 'vue'
import './js'
import * as emitter from './js/event'
import routes from './views/route'

Vue.config.productionTip = false

if (process.env.NODE_ENV === 'development') {
  const langs = [
    { lang: 'en', load: () => import('../../lang/en/front-end') },
    { lang: 'zh_CN', load: () => import('../../lang/zh_CN/front-end') },
  ]
  setTimeout(langs.find(({ lang }) => lang === blessing.locale).load, 0)
}

{
  const route = routes.find(
    // eslint-disable-next-line no-shadow
    route => (new RegExp(`^${route.path}$`, 'i')).test(blessing.route)
  )
  if (route) {
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
    } else if (route.script) {
      route.script()
    }
  }
}
