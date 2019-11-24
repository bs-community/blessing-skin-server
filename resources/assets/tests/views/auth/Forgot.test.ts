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
    refresh() { /* */ },
  },
})

test('submit forgot form', async () => {
  jest.spyOn(Date, 'now')
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValueOnce({ code: 0, message: 'ok' })
  const wrapper = mount(Forgot, { stubs: { Captcha } })
  const form = wrapper.find('form')
  const warning = wrapper.find('.alert-warning')
  const success = wrapper.find('.alert-success')

  wrapper.find('[type="email"]').setValue('a@b.c')
  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/forgot',
    { email: 'a@b.c', captcha: 'captcha' }
  )
  expect(warning.text()).toBe('fail')

  form.trigger('submit')
  await flushPromises()
  expect(success.text()).toBe('ok')
})
