import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Login from '@/views/auth/Login.vue'

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
    .mockResolvedValueOnce({ errno: 1, msg: 'fail' })
    .mockResolvedValueOnce({ errno: 1, login_fails: 4 })
    .mockResolvedValueOnce({ errno: 1, login_fails: 4 })
    .mockResolvedValueOnce({ errno: 1, login_fails: 4 })
    .mockResolvedValueOnce({ errno: 0, msg: 'ok' })
  const wrapper = mount(Login, { stubs: { Captcha } })
  const form = wrapper.find('form')
  const info = wrapper.find('.callout-info')
  const warning = wrapper.find('.callout-warning')

  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyIdentification')

  wrapper.find('input').setValue('a@b.c')
  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyPassword')

  wrapper.find('[type="password"]').setValue('123')
  form.trigger('submit')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/login',
    {
      identification: 'a@b.c', password: '123', keep: false,
    }
  )
  expect(warning.text()).toBe('fail')

  form.trigger('submit')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$alert).toBeCalledWith('auth.tooManyFails.captcha', { type: 'error' })
  expect(wrapper.find('img').exists()).toBeTrue()

  wrapper.setData({
    recaptcha: 'sitekey', invisible: true, tooManyFails: false,
  })
  form.trigger('submit')
  await wrapper.vm.$nextTick()

  wrapper.setData({
    recaptcha: 'sitekey', invisible: false, tooManyFails: false,
  })
  form.trigger('submit')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$alert).toBeCalledWith('auth.tooManyFails.recaptcha', { type: 'error' })

  wrapper.find('[type="checkbox"]').setChecked()
  form.trigger('submit')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/login',
    {
      identification: 'a@b.c', password: '123', keep: true, captcha: 'a',
    }
  )
  await wrapper.vm.$nextTick()
  jest.runAllTimers()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})
