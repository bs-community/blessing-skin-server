import { post, ResponseBody } from '../../scripts/net'
import { showModal } from '../../scripts/notify'
import { t } from '../../scripts/i18n'

export default async function handler(event: MouseEvent) {
  const button = event.target as HTMLButtonElement
  button.disabled = true

  const text = button.textContent
  button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${t('admin.downloading')}`

  const { code, message }: ResponseBody = await post('/admin/update/download')
  button.textContent = text
  button.disabled = false
  await showModal({ mode: 'alert', text: message })
  if (code === 0) {
    location.href = '/'
  }
}

const button = document.querySelector<HTMLButtonElement>('#update')
button?.addEventListener('click', handler)
