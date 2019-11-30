import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { showModal } from '@/scripts/notify'
import Login from '@/views/auth/Login.vue'

jest.mock('@/scripts/notify')

const Captcha = Vue.extend({
  methods: {
    execute() {
      return Promise.resolve('a')
    },
    refresh() { /* */ },
  },
  template: '<img>',
})

test('show captcha if too many login fails', () => {
  window.blessing.extra = { tooManyFails: true }
  const wrapper = mount(Login)
  expect(wrapper.find('img').attributes('src')).toMatch(/\/auth\/captcha\?v=\d+/)
})

test('login', async () => {
  window.blessing.extra = { tooManyFails: false }
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValueOnce({ code: 1, data: { login_fails: 4 } })
    .mockResolvedValueOnce({ code: 1, data: { login_fails: 4 } })
    .mockResolvedValueOnce({ code: 1, data: { login_fails: 4 } })
    .mockResolvedValueOnce({
      code: 0, message: 'ok', data: { redirectTo: '' },
    })
  const wrapper = mount(Login, { stubs: { Captcha } })
  const form = wrapper.find('form')
  const warning = wrapper.find('.alert-warning')

  wrapper.find('input').setValue('a@b.c')
  wrapper.find('[type="password"]').setValue('123')
  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/login',
    {
      identification: 'a@b.c', password: '123', keep: false,
    },
  )
  expect(warning.text()).toBe('fail')

  form.trigger('submit')
  await flushPromises()
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    text: 'auth.tooManyFails.captcha',
  })
  expect(wrapper.find('img').exists()).toBeTrue()

  wrapper.setData({
    recaptcha: 'sitekey', invisible: true, tooManyFails: false,
  })
  form.trigger('submit')
  await flushPromises()

  wrapper.setData({
    recaptcha: 'sitekey', invisible: false, tooManyFails: false,
  })
  form.trigger('submit')
  await flushPromises()
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    text: 'auth.tooManyFails.recaptcha',
  })

  wrapper.find('[type="checkbox"]').setChecked()
  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/login',
    {
      identification: 'a@b.c', password: '123', keep: true, captcha: 'a',
    },
  )
  await flushPromises()
  jest.runAllTimers()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})
