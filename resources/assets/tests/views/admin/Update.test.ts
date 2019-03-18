import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import Update from '@/views/admin/Update.vue'
import '@/js/notify'

jest.mock('@/js/notify')

afterEach(() => {
  window.blessing.extra = { canUpdate: true }
})
test('button should be disabled if update is unavailable', () => {
  window.blessing.extra = { canUpdate: false }
  const wrapper = mount(Update)
  expect(wrapper.find('.btn').attributes('disabled')).toBe('disabled')
})

test('perform update', async () => {
  window.$ = jest.fn(() => ({
    modal() {},
  }))
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1 })
    .mockResolvedValue({})
  Vue.prototype.$http.get
    .mockResolvedValue({ total: 2048, downloaded: 2048 })
  const wrapper = mount(Update)
  const button = wrapper.find('.btn')

  button.trigger('click')
  await flushPromises()
  expect($).not.toBeCalled()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/update/download',
    { action: 'prepare-download' }
  )
  button.trigger('click')
  jest.runOnlyPendingTimers()
  await flushPromises()
  expect($).toBeCalled()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/update/download',
    { action: 'get-progress' }
  )
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/update/download',
    { action: 'start-download' }
  )
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/update/download',
    { action: 'extract' }
  )
})
