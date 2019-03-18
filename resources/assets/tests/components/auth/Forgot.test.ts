import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Forgot from '@/components/auth/Forgot.vue'

test('click to refresh captcha', () => {
  jest.spyOn(Date, 'now')
  const wrapper = mount(Forgot)
  wrapper.find('img').trigger('click')
  expect(Date.now).toBeCalledTimes(2)
})

test('submit forgot form', async () => {
  jest.spyOn(Date, 'now')
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'fail' })
    .mockResolvedValueOnce({ errno: 0, msg: 'ok' })
  const wrapper = mount(Forgot)
  const button = wrapper.find('button')
  const info = wrapper.find('.callout-info')
  const warning = wrapper.find('.callout-warning')
  const success = wrapper.find('.callout-success')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyEmail')

  wrapper.find('[type="email"]').setValue('a')
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidEmail')

  wrapper.find('[type="email"]').setValue('a@b.c')
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyCaptcha')

  wrapper.find('[type="text"]').setValue('captcha')
  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/forgot',
    { email: 'a@b.c', captcha: 'captcha' }
  )
  expect(warning.text()).toBe('fail')
  expect(Date.now).toBeCalledTimes(2)

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(success.text()).toBe('ok')
})
