import { post } from './net'
import { t } from './i18n'
import { showModal } from './notify'
import urls from './urls'

export async function logout() {
  try {
    await showModal({
      text: t('general.confirmLogout'),
      center: true,
    })
  } catch {
    return
  }

  await post(urls.auth.logout())
  window.location.href = blessing.base_url
}

const button = document.querySelector('#logout-button')
/* istanbul ignore next */
button?.addEventListener('click', logout)
