import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Captcha from '@/components/Captcha.vue'

const VueRecaptcha = Vue.extend({
  methods: {
    execute() {
      this.$emit('verify', 'value')
    },
  },
})

test('display recaptcha', () => {
  blessing.extra = { recaptcha: 'sitekey' }
  const wrapper = mount(Captcha)
  expect(wrapper.find('img').exists()).toBeFalse()
})

test('refresh recaptcha', () => {
  const wrapper = mount<Vue & { refreshCaptcha(): void }>(Captcha)
  wrapper.vm.refreshCaptcha()
})

test('recaptcha verified', () => {
  const wrapper =
    mount<Vue & { onVerify(response: string): void, value: string }>(Captcha)
  wrapper.vm.onVerify('value')
  expect(wrapper.vm.value).toBe('value')
})

test('invoke recaptcha', async () => {
  const wrapper = mount<Vue & { execute(): Promise<string> }>(Captcha, { stubs: { VueRecaptcha } })
  wrapper.setData({ invisible: true })
  expect(await wrapper.vm.execute()).toBe('value')

  wrapper.setData({ invisible: false, value: 'haha' })
  expect(await wrapper.vm.execute()).toBe('haha')
})

test('display characters captcha', async () => {
  blessing.extra = {}
  const wrapper = mount<Vue & { execute(): Promise<string> }>(Captcha)
  expect(wrapper.find('img').exists()).toBeTrue()
  wrapper.find('input').setValue('abc')
  expect(await wrapper.vm.execute()).toBe('abc')
})

test('refresh captcha', () => {
  jest.spyOn(Date, 'now')
  const wrapper = mount(Captcha)
  wrapper.find('img').trigger('click')
  expect(Date.now).toBeCalledTimes(2)
})
