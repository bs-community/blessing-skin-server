import { flushPromises } from '../../../utils'
import { t } from '@/scripts/i18n'
import { showModal, toast } from '@/scripts/notify'
import { post } from '@/scripts/net'
import handler from '@/views/user/profile/password'

jest.mock('@/scripts/notify')
jest.mock('@/scripts/net')

test('change password', async () => {
  post
    .mockResolvedValueOnce({ code: 1, message: 'w' })
    .mockResolvedValue({ code: 0, message: 'o' })

  const form = document.createElement('form')
  form.addEventListener('submit', handler)

  const oldPassword = document.createElement('input')
  oldPassword.name = 'oldPassword'
  oldPassword.value = '1'
  form.appendChild(oldPassword)
  form.oldPassword = oldPassword

  const newPassword = document.createElement('input')
  newPassword.name = 'newPassword'
  newPassword.value = '1'
  form.appendChild(newPassword)
  form.newPassword = newPassword

  const confirm = document.createElement('input')
  confirm.name = 'confirm'
  confirm.value = '2'
  form.appendChild(confirm)
  form.confirm = confirm

  const event = new Event('submit')
  form.dispatchEvent(event)
  await flushPromises()
  expect(post).not.toBeCalled()
  expect(toast.error).toBeCalledWith(t('auth.invalidConfirmPwd'))

  confirm.value = '1'
  form.dispatchEvent(event)
  await flushPromises()
  expect(post).toBeCalledWith('/user/profile?action=password', {
    current_password: '1',
    new_password: '1',
  })
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })

  form.dispatchEvent(event)
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'o' })
})
