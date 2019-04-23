import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Register from '@/views/auth/Register.vue'
import { flushPromises } from '../../utils'

window.blessing.extra = { player: false }

const Captcha = Vue.extend({
  methods: {
    execute() {
      return Promise.resolve('captcha')
    },
    refresh() { /* */ },
  },
})

test('require player name', () => {
  window.blessing.extra = { player: true }

  const wrapper = mount(Register)
  expect(wrapper.findAll('[type="text"]').at(0)
    .attributes('placeholder')).toBe('auth.player-name')

  window.blessing.extra = { player: false }
})

test('register', async () => {
  jest.spyOn(Date, 'now')
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValueOnce({ code: 0, message: 'ok' })
  const wrapper = mount(Register, { stubs: { Captcha } })
  const form = wrapper.find('form')
  const info = wrapper.find('.callout-info')
  const warning = wrapper.find('.callout-warning')

  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyEmail')

  wrapper.find('[type="email"]').setValue('a')
  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.invalidEmail')

  wrapper.find('[type="email"]').setValue('a@b.c')
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
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyNickname')

  wrapper.findAll('[type="text"]').at(0)
    .setValue('abc')
  form.trigger('submit')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/register',
    {
      email: 'a@b.c',
      password: '12345678',
      nickname: 'abc',
      captcha: 'captcha',
    }
  )
  await wrapper.vm.$nextTick()
  expect(warning.text()).toBe('fail')

  form.trigger('submit')
  await flushPromises()
  jest.runAllTimers()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})

test('register with player name', async () => {
  window.blessing.extra = { player: true }
  Vue.prototype.$http.post.mockResolvedValue({ code: 0, message: 'ok' })
  const wrapper = mount(Register, { stubs: { Captcha } })
  const form = wrapper.find('form')
  const info = wrapper.find('.callout-info')
  wrapper.find('[type="email"]').setValue('a@b.c')
  wrapper.findAll('[type="password"]').at(0)
    .setValue('12345678')
  wrapper.findAll('[type="password"]').at(1)
    .setValue('12345678')

  form.trigger('submit')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(info.text()).toBe('auth.emptyPlayerName')

  wrapper.findAll('[type="text"]').at(0)
    .setValue('abc')
  form.trigger('submit')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/auth/register',
    {
      email: 'a@b.c',
      password: '12345678',
      player_name: 'abc',
      captcha: 'captcha',
    }
  )
})
