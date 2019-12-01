import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../utils'
import { toast } from '@/scripts/notify'
import EmailVerification from '@/components/EmailVerification.vue'

jest.mock('@/scripts/notify')

test('message box should not be render if verified', () => {
  window.blessing.extra = { unverified: false }
  const wrapper = mount(EmailVerification)
  expect(wrapper.isEmpty()).toBeTrue()
})

test('resend email', async () => {
  window.blessing.extra = { unverified: true }
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  const wrapper = mount(EmailVerification)
  const button = wrapper.find('a')

  button.trigger('click')
  await flushPromises()
  expect(toast.error).toBeCalledWith('1')

  button.trigger('click')
  await flushPromises()
  expect(toast.success).toBeCalledWith('0')
})
