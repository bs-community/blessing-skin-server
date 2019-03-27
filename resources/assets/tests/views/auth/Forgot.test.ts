import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Forgot from '@/views/auth/Forgot.vue'
import { flushPromises } from '../../utils'

window.blessing.extra = {}
const Captcha = Vue.extend({
  methods: {
    execute() {
      return Promise.resolve('captcha')
    },
    refreshCaptcha() { /* */ },
  },
})

test('submit forgot form', async () => {
  jest.spyOn(Date, 'now')
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'fail' })
    .mockResolvedValueOnce({ errno: 0, msg: 'ok' })
  const wrapper = mount(Forgot, { stubs: { Captcha } })
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
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/forgot',
    { email: 'a@b.c', captcha: 'captcha' }
  )
  expect(warning.text()).toBe('fail')

  button.trigger('click')
  await flushPromises()
  expect(success.text()).toBe('ok')
})
