import { expect, vi, test } from 'vitest'
import { flushPromises } from '../../../utils'
import { showModal } from '@/scripts/notify'
import { post } from '@/scripts/net'
import handler from '@/views/user/profile/email'

vi.mock('@/scripts/notify')
vi.mock('@/scripts/net')

test('change email', async () => {
  post
    .mockResolvedValueOnce({ code: 1, message: 'w' })
    .mockResolvedValue({ code: 0, message: 'o' })

  const form = document.createElement('form')
  form.addEventListener('submit', handler)

  const email = document.createElement('input')
  email.name = 'email'
  email.value = 'a@b.c'
  form.appendChild(email)
  form.email = email

  const password = document.createElement('input')
  password.name = 'password'
  password.value = 'abc'
  form.appendChild(password)
  form.password = password

  const event = new Event('submit')
  form.dispatchEvent(event)
  await flushPromises()
  expect(post).toBeCalledWith('/user/profile?action=email', {
    email: 'a@b.c',
    password: 'abc',
  })
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })

  form.dispatchEvent(event)
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'o' })
})
