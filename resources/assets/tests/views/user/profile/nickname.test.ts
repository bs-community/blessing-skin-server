import { expect, vi, test } from 'vitest'
import { flushPromises } from '../../../utils'
import { showModal } from '@/scripts/notify'
import { post } from '@/scripts/net'
import handler from '@/views/user/profile/nickname'

vi.mock('@/scripts/notify')
vi.mock('@/scripts/net')

test('change nickname', async () => {
  document.body.innerHTML = '<span data-mark="nickname"></span>'
  post
    .mockResolvedValueOnce({ code: 1, message: 'w' })
    .mockResolvedValue({ code: 0, message: 'o' })

  const form = document.createElement('form')
  form.addEventListener('submit', handler)

  const nickname = document.createElement('input')
  nickname.name = 'nickname'
  nickname.value = 'nickname'
  form.appendChild(nickname)
  form.nickname = nickname

  const event = new Event('submit')
  form.dispatchEvent(event)
  await flushPromises()
  expect(post).toBeCalledWith('/user/profile?action=nickname', {
    new_nickname: 'nickname',
  })
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })

  form.dispatchEvent(event)
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'o' })
  expect(document.querySelector('span')!.textContent).toBe('nickname')
})
