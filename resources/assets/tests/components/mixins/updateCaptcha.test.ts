import { mount } from '@vue/test-utils'
import updateCaptcha from '@/components/mixins/updateCaptcha'

test('update captcha', () => {
  const wrapper = mount(updateCaptcha)
  wrapper.vm.updateCaptcha('value')
  expect(wrapper.vm.captcha).toBe('value')
})
