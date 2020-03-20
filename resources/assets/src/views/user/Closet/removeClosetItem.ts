import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'

export default async function removeClosetItem(tid: number): Promise<boolean> {
  try {
    await showModal({
      text: t('user.removeFromClosetNotice'),
      okButtonType: 'danger',
    })
  } catch {
    return false
  }

  const { code, message } = await fetch.post<fetch.ResponseBody>(
    `/user/closet/remove/${tid}`,
  )
  if (code === 0) {
    toast.success(message)
  } else {
    toast.error(message)
  }

  return code === 0
}
