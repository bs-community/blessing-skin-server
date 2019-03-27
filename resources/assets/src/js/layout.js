import 'bootstrap' // eslint-disable-line import/no-extraneous-dependencies
import Vue from 'vue'

Vue.mixin({
  mounted() {
    $('[data-toggle="tooltip"]').tooltip()
  },
})

document.addEventListener('loadend', () => {
  $('[data-toggle="tooltip"]').tooltip()
})

;(() => {
  const list = [
    {
      path: 'admin',
      styl: () => import('../stylus/admin.styl'),
    },
    {
      path: 'auth',
      styl: () => import('../stylus/auth.styl'),
    },
  ]
  const style = list.find(({ path }) => blessing.route.startsWith(path))
  if (style) {
    style.styl()
  }
})()
