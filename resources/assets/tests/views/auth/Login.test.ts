import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Login from '@/views/auth/Login.vue'
import { swal } from '@/js/notify'

jest.mock('@/js/notify')

test('show captcha if too many login fails', () => {
  window.blessing.extra = { tooManyFails: true }
  const wrapper = mount(Login)
  expect(wrapper.find('img').attributes('src')).toMatch(/\/auth\/captcha\?v=\d+/)
})

test('click to refresh captcha', () => {
  window.blessing.extra = { tooManyFails: true }
  jest.spyOn(Date, 'now')
  const wrapper = mount(Login)
  wrapper.find('img').trigger('click')
  expect(Date.now).toBeCalledTimes(2)
})

test('login', async () => {
  window.blessing.extra = { tooManyFails: false }
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: 'fail' })
    .mockResolvedValueOnce({ errno: 1, login_fails: 4 })
    .mockResolvedValueOnce({ errno: 0, msg: 'ok' })
  const wrapper = mount(Login)
  const button = wrapper.find('button')
  const info = wrapper.find('.callout-info')
  const warning = wrapper.find('.callout-warning')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyIdentification')

  wrapper.find('[type="email"]').setValue('a@b.c')
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyPassword')

  wrapper.find('[type="password"]').setValue('123')
  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/login',
    {
      identification: 'a@b.c', password: '123', keep: false,
    }
  )
  expect(warning.text()).toBe('fail')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(swal).toBeCalledWith({ type: 'error', text: 'auth.tooManyFails' })
  expect(wrapper.find('img').exists()).toBeTrue()

  button.trigger('click')
  expect(info.text()).toBe('auth.emptyCaptcha')

  wrapper.find('[type="text"]').setValue('a')
  wrapper.find('[type="checkbox"]').setChecked()
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/login',
    {
      identification: 'a@b.c', password: '123', keep: true, captcha: 'a',
    }
  )
  await wrapper.vm.$nextTick()
  jest.runAllTimers()
  expect(swal).toBeCalledWith({ type: 'success', text: 'ok' })
})
