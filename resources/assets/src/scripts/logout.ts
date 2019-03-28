import { Message, MessageBox } from './element'
import { post } from './net'
import { trans } from './i18n'

export async function logout() {
  try {
    await MessageBox.confirm(trans('general.confirmLogout'), {
      type: 'warning',
    })
  } catch {
    return
  }

  const { msg } = await post('/auth/logout')
  setTimeout(() => (window.location.href = blessing.base_url), 1000)
  Message.success(msg)
}

const button = document.querySelector('#logout-button')
/* istanbul ignore next, not all pages contains this button. */
if (button) {
  button.addEventListener('click', logout)
}
