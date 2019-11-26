import { Message, MessageBox } from 'element-ui'
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

  const { message } = await post('/auth/logout')
  setTimeout(() => (window.location.href = document.baseURI), 1000)
  Message.success(message)
}

const button = document.querySelector('#logout-button')
/* istanbul ignore next, not all pages contains this button. */
if (button) {
  button.addEventListener('click', logout)
}
