import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../utils'
import AddPlayerDialog from '@/components/AddPlayerDialog.vue'

window.blessing.extra = {
  rule: 'rule',
  length: 'length',
}

test('add player', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  Vue.prototype.$http.get.mockResolvedValueOnce([])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValue({ code: 0, message: 'ok' })
  const wrapper = mount(AddPlayerDialog)
  const button = wrapper.find('[data-test=addPlayer]')
  wrapper.find('input[type="text"]').setValue('the-new')

  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/add',
    { name: 'the-new' },
  )
  await flushPromises()
  expect(wrapper.text()).not.toContain('the-new')
  expect(Vue.prototype.$message.warning).toBeCalledWith('fail')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.emitted().add).toBeDefined()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})
