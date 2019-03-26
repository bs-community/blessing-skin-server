import Vue from 'vue'
import { mount } from '@vue/test-utils'
import EmailVerification from '@/components/EmailVerification.vue'

test('message box should not be render if verified', () => {
  window.blessing.extra = { unverified: false }
  const wrapper = mount(EmailVerification)
  expect(wrapper.isEmpty()).toBeTrue()
})

test('resend email', async () => {
  window.blessing.extra = { unverified: true }
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  const wrapper = mount(EmailVerification)
  const button = wrapper.find('a')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$message.error).toBeCalledWith('1')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$message.success).toBeCalledWith('0')
})
