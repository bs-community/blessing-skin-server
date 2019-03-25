import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import Profile from '@/views/user/Profile.vue'

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
  Vue.prototype.$confirm
    .mockRejectedValueOnce('close')
    .mockResolvedValue('confirm')
  Vue.prototype.$http.post.mockResolvedValue({ msg: 'ok' })
  const wrapper = mount(Profile)
  const button = wrapper.find('[data-test=resetAvatar]')
  document.body.innerHTML += '<img alt="User Image" src="a">'

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile/avatar',
    { tid: 0 }
  )
  await flushPromises()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
  expect(document.querySelector('img')!.src).toMatch(/\d+$/)
})

test('change password', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'w' })
    .mockResolvedValueOnce({ errno: 0, msg: 'o' })
  const wrapper = mount(Profile)
  const button = wrapper.find('[data-test=changePassword]')

  button.trigger('click')
  expect(Vue.prototype.$message.error).toBeCalledWith('user.emptyPassword')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ oldPassword: '1' })
  button.trigger('click')
  expect(Vue.prototype.$message.error).toBeCalledWith('user.emptyNewPassword')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ newPassword: '1' })
  button.trigger('click')
  expect(Vue.prototype.$message.error).toBeCalledWith('auth.emptyConfirmPwd')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ confirmPassword: '2' })
  button.trigger('click')
  expect(Vue.prototype.$message.error).toBeCalledWith('auth.invalidConfirmPwd')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ confirmPassword: '1' })
  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=password',
    { current_password: '1', new_password: '1' }
  )
  expect(Vue.prototype.$alert).toBeCalledWith('w', { type: 'warning' })

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$alert).toBeCalledWith('o')
})

test('change nickname', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'w' })
    .mockResolvedValue({ errno: 0, msg: 'o' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce('close')
    .mockResolvedValue('confirm')
  const wrapper = mount(Profile)
  const button = wrapper.find('[data-test=changeNickName]')
  document.body.innerHTML += '<span class="nickname"></span>'

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(Vue.prototype.$alert).toBeCalledWith('user.emptyNewNickName', { type: 'error' })

  wrapper.setData({ nickname: 'nickname' })
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(Vue.prototype.$confirm).toBeCalledWith('user.changeNickName')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=nickname',
    { new_nickname: 'nickname' }
  )
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$alert).toBeCalledWith('w', { type: 'warning' })

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$message.success).toBeCalledWith('o')
  expect(document.querySelector('.nickname')!.textContent).toBe('nickname')
})

test('change email', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'w' })
    .mockResolvedValue({ errno: 0, msg: 'o' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce('close')
    .mockResolvedValue('confirm')
  const wrapper = mount(Profile)
  const button = wrapper.find('[data-test=changeEmail]')

  button.trigger('click')
  expect(Vue.prototype.$alert).toBeCalledWith('user.emptyNewEmail', { type: 'error' })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ email: 'e' })
  button.trigger('click')
  expect(Vue.prototype.$alert).toBeCalledWith('auth.invalidEmail', { type: 'warning' })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ email: 'a@b.c', currentPassword: 'abc' })
  button.trigger('click')
  expect(Vue.prototype.$confirm).toBeCalledWith('user.changeEmail')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=email',
    { new_email: 'a@b.c', password: 'abc' }
  )
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$alert).toBeCalledWith('w', { type: 'warning' })

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$message.success).toBeCalledWith('o')
})

test('delete account', async () => {
  window.blessing.extra = { admin: true }
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'w' })
    .mockResolvedValue({ errno: 0, msg: 'o' })
  const wrapper = mount(Profile)
  const button = wrapper.find('[data-test=deleteAccount]')

  button.trigger('click')
  expect(Vue.prototype.$alert).toBeCalledWith('user.emptyDeletePassword', { type: 'error' })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ deleteConfirm: 'abc' })
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/profile?action=delete',
    { password: 'abc' }
  )
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$alert).toBeCalledWith('w', { type: 'warning' })

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$alert).toBeCalledWith('o', { type: 'success' })
})
