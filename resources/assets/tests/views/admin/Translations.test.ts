import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { Button } from 'element-ui'
import { MessageBoxData } from 'element-ui/types/message-box'
import { flushPromises } from '../../utils'
import Translations from '@/views/admin/Translations.vue'

test('fetch data', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      id: 1, group: 'general', key: 'submit', text: '',
    },
  ])

  const wrapper = mount(Translations)
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledWith('/admin/i18n/list')
  expect(wrapper.text()).toContain('admin.i18n.empty')
})

test('modify line', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      id: 1, group: 'general', key: 'submit', text: '',
    },
  ])
  Vue.prototype.$http.put
    .mockResolvedValueOnce({ code: 1, message: 'failed' })
    .mockResolvedValueOnce({ code: 0, message: 'ok' })
  Vue.prototype.$prompt
    .mockRejectedValueOnce(null)
    .mockResolvedValueOnce({ value: '' } as MessageBoxData)
    .mockResolvedValueOnce({ value: 'wanshengwei' } as MessageBoxData)

  const wrapper = mount(Translations)
  await flushPromises()
  const button = wrapper.findAll(Button).at(0)

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.put).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.put).toBeCalledWith(
    '/admin/i18n',
    { id: 1, text: '' }
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('failed')
  expect(wrapper.text()).not.toContain('wanshengwei')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.put).toBeCalledWith(
    '/admin/i18n',
    { id: 1, text: 'wanshengwei' }
  )
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
  expect(wrapper.text()).toContain('wanshengwei')
})

test('delete line', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      id: 1, group: 'general', key: 'submit', text: '',
    },
  ])
  Vue.prototype.$http.del.mockResolvedValueOnce({ message: 'ok' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce(null)
    .mockResolvedValueOnce('confirm')

  const wrapper = mount(Translations)
  await flushPromises()
  const button = wrapper.findAll(Button).at(1)

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.del).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.del).toBeCalledWith('/admin/i18n', { id: 1 })
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
  expect(wrapper.text()).not.toContain('general')
})
