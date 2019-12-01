import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { showModal, toast } from '@/scripts/notify'
import Profile from '@/views/user/Profile.vue'

jest.mock('@/scripts/notify')

window.blessing.extra = { unverified: false }

test('computed values', () => {
  window.blessing.extra = { admin: true }
  const wrapper = mount<Vue & { siteName: string, isAdmin: boolean }>(Profile)
  expect(wrapper.vm.siteName).toBe('Blessing Skin')
  expect(wrapper.vm.isAdmin).toBeTrue()
  window.blessing.extra = { admin: false }
  expect(mount<Vue & { isAdmin: boolean }>(Profile).vm.isAdmin).toBeFalse()
})

test('convert linebreak', () => {
  const wrapper = mount<Vue & { nl2br(input: string): string }>(Profile)
  expect(wrapper.vm.nl2br('a\nb\nc')).toBe('a<br>b<br>c')
})

test('reset avatar', async () => {
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: '' })
  Vue.prototype.$http.post.mockResolvedValue({ message: 'ok' })
  const wrapper = mount(Profile)
  const button = wrapper.find('[data-test=resetAvatar]')
  document.body.innerHTML += '<img alt="User Image" src="a">'

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile/avatar',
    { tid: 0 },
  )
  await flushPromises()
  expect(toast.success).toBeCalledWith('ok')
  expect(document.querySelector('img')!.src).toMatch(/\d+$/)
})

test('change password', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'w' })
    .mockResolvedValueOnce({ code: 0, message: 'o' })
  const wrapper = mount(Profile)
  const form = wrapper.find('[data-test=changePassword]')

  wrapper.setData({ oldPassword: '1' })
  wrapper.setData({ newPassword: '1' })
  wrapper.setData({ confirmPassword: '2' })
  form.trigger('submit')
  expect(toast.error).toBeCalledWith('auth.invalidConfirmPwd')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ confirmPassword: '1' })
  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=password',
    { current_password: '1', new_password: '1' },
  )
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })

  form.trigger('submit')
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'o' })
})

test('change nickname', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'w' })
    .mockResolvedValue({ code: 0, message: 'o' })
  const wrapper = mount(Profile)
  const form = wrapper.find('[data-test=changeNickName]')
  document.body.innerHTML += '<span data-mark="nickname"></span>'

  wrapper.setData({ nickname: 'nickname' })
  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=nickname',
    { new_nickname: 'nickname' },
  )
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })

  form.trigger('submit')
  await flushPromises()
  expect(toast.success).toBeCalledWith('o')
  expect(document.querySelector('span')!.textContent).toBe('nickname')
})

test('change email', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'w' })
    .mockResolvedValue({ code: 0, message: 'o' })
  const wrapper = mount(Profile)
  const form = wrapper.find('[data-test=changeEmail]')

  wrapper.setData({ email: 'a@b.c', currentPassword: 'abc' })
  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=email',
    { new_email: 'a@b.c', password: 'abc' },
  )
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })

  form.trigger('submit')
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'o' })
})

test('delete account', async () => {
  window.blessing.extra = { admin: true }
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'w' })
    .mockResolvedValue({ code: 0, message: 'o' })
  const wrapper = mount(Profile)
  const form = wrapper.find('[data-test=deleteAccount]')

  wrapper.setData({ deleteConfirm: 'abc' })
  form.trigger('submit')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=delete',
    { password: 'abc' },
  )
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })

  form.trigger('submit')
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'w' })
})
