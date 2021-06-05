import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import type { Texture } from '@/scripts/types'
import urls from '@/scripts/urls'

export default async function addClosetItem(
  texture: Pick<Texture, 'tid' | 'name'>,
): Promise<boolean> {
  let name: string
  try {
    const { value } = await showModal({
      mode: 'prompt',
      title: t('skinlib.setItemName'),
      text: t('skinlib.applyNotice'),
      input: texture.name,
      validator: (value: string) => {
        if (!value) {
          return t('skinlib.emptyItemName')
        }
      },
    })
    name = value
  } catch {
    return false
  }

  const { code, message } = await fetch.post<fetch.ResponseBody>(
    urls.user.closet.add(),
    { tid: texture.tid, name },
  )
  if (code === 0) {
    toast.success(message)
  } else {
    toast.error(message)
  }

  return code === 0
}
