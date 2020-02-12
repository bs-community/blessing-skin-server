import './init' // Must be first
import 'bootstrap'
import 'admin-lte'
import './i18n'
import './net'
import './event'
import './notification'
import './emailVerification'
import './logout'

window.addEventListener('load', () => {
  $('[data-toggle="tooltip"]').tooltip()
})
