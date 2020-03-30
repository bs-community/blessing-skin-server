import * as React from 'react'
import ReactDOM from 'react-dom'
import './scripts/app'
import routes from './scripts/route'
import * as emitter from './scripts/event'

loadModules()

async function loadModules() {
  if (blessing.route.startsWith('admin')) {
    const entry = document.querySelector<HTMLAnchorElement>('#launch-cli')
    entry?.addEventListener('click', async () => {
      const { launch } = await import('./scripts/cli')
      launch()
    })
  }

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
        route.react as () => Promise<{ default: React.ComponentType }>,
      )
      const Root = () => (
        <React.Suspense fallback={route.frame?.() ?? ''}>
          <Component />
        </React.Suspense>
      )
      const c =
        typeof route.el === 'string'
          ? document.querySelector(route.el)
          : route.el
      ReactDOM.render(<Root />, c, () => {
        setTimeout(() => emitter.emit('mounted', { el: route.el }), 0)
      })
    }
    if (route.component) {
      const { default: Vue } = await import('vue')
      const { default: inject } = await import('./scripts/injectVue')
      inject(Vue)
      Vue.prototype.$route = new RegExp(`^${route.path}$`, 'i').exec(
        blessing.route,
      )
      // eslint-disable-next-line no-new
      new Vue({
        el: route.el,
        render: h => h(route.component),
      })
    }
  }
}
