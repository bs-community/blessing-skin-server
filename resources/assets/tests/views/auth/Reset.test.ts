import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Reset from '@/views/auth/Reset.vue'
import { swal } from '@/js/notify'

jest.mock('@/js/notify')

test('reset password', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'fail' })
    .mockResolvedValueOnce({ errno: 0, msg: 'ok' })
  const wrapper = mount(Reset, {
    mocks: {
      $route: ['/auth/reset/1', '1'],
    },
  })
  const button = wrapper.find('button')
  const info = wrapper.find('.callout-info')
  const warning = wrapper.find('.callout-warning')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyPassword')

  wrapper.findAll('[type="password"]').at(0)
    .setValue('123456')
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidPassword')

  wrapper.findAll('[type="password"]').at(0)
    .setValue('12345678')
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidConfirmPwd')

  wrapper.findAll('[type="password"]').at(1)
    .setValue('123456')
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidConfirmPwd')

  wrapper.findAll('[type="password"]').at(1)
    .setValue('12345678')
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/reset/1', // Ignore `location.search`
    { password: '12345678' }
  )
  await wrapper.vm.$nextTick()
  expect(warning.text()).toBe('fail')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(swal).toBeCalledWith({ type: 'success', text: 'ok' })
})
