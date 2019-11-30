import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { showModal } from '@/scripts/notify'
import Translations from '@/views/admin/Translations.vue'

jest.mock('@/scripts/notify')

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
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValueOnce({ value: '' })
    .mockResolvedValueOnce({ value: 'wanshengwei' })

  const wrapper = mount(Translations)
  await flushPromises()
  const button = wrapper.find('.btn-default')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.put).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.put).toBeCalledWith(
    '/admin/i18n',
    { id: 1, text: '' },
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('failed')
  expect(wrapper.text()).not.toContain('wanshengwei')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.put).toBeCalledWith(
    '/admin/i18n',
    { id: 1, text: 'wanshengwei' },
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
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValueOnce({ value: '' })

  const wrapper = mount(Translations)
  await flushPromises()
  const button = wrapper.find('.btn-danger')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.del).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.del).toBeCalledWith('/admin/i18n', { id: 1 })
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
  expect(wrapper.text()).not.toContain('general')
})
