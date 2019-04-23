import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Reset from '@/views/auth/Reset.vue'

test('reset password', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValueOnce({ code: 0, message: 'ok' })
  const wrapper = mount(Reset, {
    mocks: {
      $route: ['/auth/reset/1', '1'],
    },
  })
  const form = wrapper.find('form')
  const info = wrapper.find('.callout-info')
  const warning = wrapper.find('.callout-warning')

  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyPassword')

  wrapper.findAll('[type="password"]').at(0)
    .setValue('123456')
  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidPassword')

  wrapper.findAll('[type="password"]').at(0)
    .setValue('12345678')
  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidConfirmPwd')

  wrapper.findAll('[type="password"]').at(1)
    .setValue('123456')
  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidConfirmPwd')

  wrapper.findAll('[type="password"]').at(1)
    .setValue('12345678')
  form.trigger('submit')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/reset/1', // Ignore `location.search`
    { password: '12345678' }
  )
  await wrapper.vm.$nextTick()
  expect(warning.text()).toBe('fail')

  form.trigger('submit')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})
