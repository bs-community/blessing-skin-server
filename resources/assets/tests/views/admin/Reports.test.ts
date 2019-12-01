import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { toast } from '@/scripts/notify'
import Reports from '@/views/admin/Reports.vue'

jest.mock('@/scripts/notify')

test('basic render', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [{
      id: 1,
      uploader: 1,
      uploaderName: 'a',
      reporter: 2,
      reporterName: 'b',
      reason: 'sth',
      status: 0,
    }],
  })
  const wrapper = mount(Reports)
  await flushPromises()
  const text = wrapper.text()
  expect(text).toContain('a (UID: 1)')
  expect(text).toContain('b (UID: 2)')
  expect(text).toContain('sth')
  expect(text).toContain('report.status.0')
})

test('link to skin library', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [{ id: 1, tid: 1 }],
  })
  const wrapper = mount(Reports)
  await flushPromises()
  expect(wrapper.find('a').attributes('href')).toBe('/skinlib/show/1')
})

test('delete texture', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [{ id: 1, status: 0 }] })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValue({
      code: 0, message: 'ok', data: { status: 1 },
    })
  const wrapper = mount(Reports)
  await flushPromises()
  const button = wrapper.findAll('a').at(1)

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/reports',
    { id: 1, action: 'delete' },
  )
  expect(toast.error).toBeCalledWith('fail')

  button.trigger('click')
  await flushPromises()
  expect(toast.success).toBeCalledWith('ok')
  expect(wrapper.text()).toContain('report.status.1')
})

test('ban uploader', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [{ id: 1, status: 0 }] })
  Vue.prototype.$http.post
    .mockResolvedValue({
      code: 0, message: 'ok', data: { status: 1 },
    })
  const wrapper = mount(Reports)
  await flushPromises()
  const button = wrapper.findAll('a').at(2)

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/reports',
    { id: 1, action: 'ban' },
  )
  expect(toast.success).toBeCalledWith('ok')
  expect(wrapper.text()).toContain('report.status.1')
})

test('reject', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [{ id: 1, status: 0 }] })
  Vue.prototype.$http.post
    .mockResolvedValue({
      code: 0, message: 'ok', data: { status: 2 },
    })
  const wrapper = mount(Reports)
  await flushPromises()
  const button = wrapper.find('button')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/reports',
    { id: 1, action: 'reject' },
  )
  expect(toast.success).toBeCalledWith('ok')
  expect(wrapper.text()).toContain('report.status.2')
})
