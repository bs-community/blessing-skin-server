import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Captcha from '@/components/Captcha.vue'

test('display recaptcha', () => {
  blessing.extra = { recaptcha: 'sitekey' }
  const wrapper = mount(Captcha)
  expect(wrapper.find('img').exists()).toBeFalse()
})

test('refresh recaptcha', () => {
  const wrapper = mount<Vue & { refreshCaptcha(): void }>(Captcha)
  wrapper.vm.refreshCaptcha()
})

test('display characters captcha', () => {
  blessing.extra = {}
  const wrapper = mount(Captcha)
  expect(wrapper.find('img').exists()).toBeTrue()
  wrapper.find('input').setValue('abc')
  expect(wrapper.emitted().change[0][0]).toBe('abc')
})

test('refresh captcha', () => {
  jest.spyOn(Date, 'now')
  const wrapper = mount(Captcha)
  wrapper.find('img').trigger('click')
  expect(Date.now).toBeCalledTimes(2)
})
