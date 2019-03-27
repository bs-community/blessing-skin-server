/* eslint-disable import/no-extraneous-dependencies */
import 'bootstrap/js/dropdown'
import 'bootstrap/js/modal'
import 'admin-lte/build/js/Layout'
import 'admin-lte/build/js/PushMenu'
import 'admin-lte/build/js/Tree'

(() => {
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
