import { post } from './net'
import { trans } from './i18n'
import { showModal } from './notify'

export async function logout() {
  try {
    await showModal({
      text: trans('general.confirmLogout'),
      center: true,
    })
  } catch {
    return
  }

  await post('/auth/logout')
  window.location.href = blessing.base_url
}

const button = document.querySelector('#logout-button')
/* istanbul ignore next, not all pages contains this button. */
if (button) {
  button.addEventListener('click', logout)
}
