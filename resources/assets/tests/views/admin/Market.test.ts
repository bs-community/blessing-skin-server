import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { showModal } from '@/scripts/notify'
import Market from '@/views/admin/Market.vue'

jest.mock('@/scripts/notify')

test('render dependencies', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { name: 'a', dependencies: { all: {}, unsatisfied: {} } },
    {
      name: 'b',
      dependencies: {
        all: { a: '^1.0.0', c: '^2.0.0' }, unsatisfied: { c: {} },
      },
    },
  ])
  const wrapper = mount(Market)
  await flushPromises()

  expect(wrapper.text()).toContain('admin.noDependencies')
  expect(wrapper.find('span.badge.bg-green').text()).toBe('a: ^1.0.0')
  expect(wrapper.find('span.badge.bg-red').text()).toBe('c: ^2.0.0')
})

test('render operation buttons', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { all: {}, unsatisfied: {} }, installed: true, can_update: true,
    },
    {
      name: 'b', dependencies: { all: {}, unsatisfied: {} }, installed: true, enabled: true,
    },
    {
      name: 'c', dependencies: { all: {}, unsatisfied: {} }, installed: true,
    },
    {
      name: 'd', dependencies: { all: {}, unsatisfied: {} }, installed: false,
    },
  ])
  const wrapper = mount(Market)
  await flushPromises()
  const tbody = wrapper.find('tbody')

  expect(tbody.find('tr:nth-child(1)').text()).toContain('admin.updatePlugin')
  expect(tbody.find('tr:nth-child(2)').text()).toContain('admin.statusEnabled')
  expect(tbody.find('tr:nth-child(3)').text()).toContain('admin.enablePlugin')
  expect(tbody.find('tr:nth-child(4)').text()).toContain('admin.installPlugin')
})

test('install plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'd', dependencies: { all: {}, unsatisfied: {} }, installed: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({
      code: 1,
      message: 'unresolved',
      data: { reason: ['u'] },
    })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  const wrapper = mount(Market)
  await flushPromises()
  const button = wrapper.find('button')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/market/download',
    { name: 'd' },
  )

  button.trigger('click')
  await flushPromises()
  expect(showModal).toBeCalledWith(expect.objectContaining({ mode: 'alert' }))

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('admin.enablePlugin')
})

test('update plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a',
      version: '2.0.0',
      dependencies: { all: {}, unsatisfied: {} },
      installed: '1.0.0',
      can_update: true,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: '' })
  const wrapper = mount(Market)
  await flushPromises()
  const button = wrapper.find('button')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/market/download',
    { name: 'a' },
  )
})
