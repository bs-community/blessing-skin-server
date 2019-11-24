import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
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
  const info = wrapper.find('.alert-info')
  const warning = wrapper.find('.alert-warning')

  wrapper.findAll('[type="password"]').at(0)
    .setValue('12345678')
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
  await flushPromises()
  expect(warning.text()).toBe('fail')

  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})
