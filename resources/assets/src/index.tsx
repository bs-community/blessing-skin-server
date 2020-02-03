import Vue from 'vue'
import * as React from 'react'
import ReactDOM from 'react-dom'
import './scripts/app'
import routes from './scripts/route'

Vue.config.productionTip = false

loadModules()

function loadModules() {
  const route = routes.find(
    // eslint-disable-next-line no-shadow
    route => new RegExp(`^${route.path}$`, 'i').test(blessing.route),
  )
  if (route) {
    if (route.module) {
      Promise.all(route.module.map(m => m()))
    }
    if (route.react) {
      const Component = React.lazy(
        route.react as (() => Promise<{ default: React.ComponentType }>)
      )
      const Root = () => (
        <React.Suspense fallback={''}>
          <Component />
        </React.Suspense>
      )
      ReactDOM.render(<Root />, document.querySelector(route.el))
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
