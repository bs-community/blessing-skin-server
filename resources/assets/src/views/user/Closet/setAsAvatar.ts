import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import urls from '@/scripts/urls'

export default async function setAsAvatar(tid: number) {
  try {
    await showModal({
      title: t('user.setAvatar'),
      text: t('user.setAvatarNotice'),
    })
  } catch {
    return
  }

  const { code, message } = await fetch.post<fetch.ResponseBody>(
    urls.user.profile.avatar(),
    { tid },
  )
  if (code === 0) {
    toast.success(message)
    document
      .querySelectorAll<HTMLImageElement>('[alt="User Image"]')
      .forEach((el) => {
        el.src = `${blessing.base_url}/avatar/${tid}`
      })
  } else {
    toast.error(message)
  }
}
