import { post } from './net'
import { swal } from './notify'
import { trans } from './i18n'

export async function logout() {
  const { dismiss } = await swal({
    text: trans('general.confirmLogout'),
    type: 'warning',
    showCancelButton: true,
    confirmButtonText: trans('general.confirm'),
    cancelButtonText: trans('general.cancel'),
  })
  if (dismiss) {
    return
  }

  const { msg } = await post('/auth/logout')
  setTimeout(() => (window.location.href = blessing.base_url), 1000)
  swal({ type: 'success', text: msg })
}

const button = document.querySelector('#logout-button')
/* istanbul ignore next, not all pages contains this button. */
if (button) {
  button.addEventListener('click', logout)
}
