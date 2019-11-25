import './public-path' // Must be first
import $ from 'jquery'
import 'bootstrap'
import 'admin-lte'
import './i18n'
import './net'
import './event'
import './notification'
import './element'
import './logout'

window.addEventListener('load', () => {
  // @ts-ignore
  $('[data-toggle="tooltip"]').tooltip()
})
