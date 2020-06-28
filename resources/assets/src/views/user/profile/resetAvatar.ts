import { showModal, toast } from '@/scripts/notify'
import { t } from '@/scripts/i18n'
import { post, ResponseBody } from '@/scripts/net'

export default async function resetAvatar() {
  try {
    await showModal({ text: t('user.resetAvatarConfirm') })
  } catch {
    return
  }

  const { message }: ResponseBody = await post('/user/profile/avatar', {
    tid: 0,
  })
  toast.success(message)
  document
    .querySelectorAll<HTMLImageElement>('[alt="User Image"]')
    .forEach((el) => {
      el.src = `${blessing.base_url}/avatar/0`
    })
}
