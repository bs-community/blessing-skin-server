import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../utils'
import { toast } from '@/scripts/notify'
import Modal from '@/components/Modal.vue'
import AddPlayerDialog from '@/components/AddPlayerDialog.vue'

jest.mock('@/scripts/notify')

window.blessing.extra = {
  rule: 'rule',
  length: 'length',
}

test('add player', async () => {
  Vue.prototype.$http.get.mockResolvedValueOnce([])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValue({ code: 0, message: 'ok' })
  const wrapper = mount(AddPlayerDialog)
  const modal = wrapper.find(Modal)
  wrapper.find('input[type="text"]').setValue('the-new')

  modal.vm.$emit('confirm')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/add',
    { name: 'the-new' },
  )
  await flushPromises()
  expect(wrapper.text()).not.toContain('the-new')
  expect(toast.error).toBeCalledWith('fail')

  modal.vm.$emit('confirm')
  await flushPromises()
  expect(wrapper.emitted().add).toBeDefined()
  expect(toast.success).toBeCalledWith('ok')
})
