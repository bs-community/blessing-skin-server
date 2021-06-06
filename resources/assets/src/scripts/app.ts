import './init' // Must be first
import './extra'
import './i18n'
import './net'
import './event'
import './notification'
import './emailVerification'
import './logout'
import './darkMode'

window.addEventListener('load', () => {
  $('[data-toggle="tooltip"]').tooltip()
})
