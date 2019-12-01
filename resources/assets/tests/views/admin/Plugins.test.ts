import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { showModal, toast } from '@/scripts/notify'
import Plugins from '@/views/admin/Plugins.vue'

jest.mock('@/scripts/notify')

test('render dependencies', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { name: 'a', dependencies: { all: {}, unsatisfied: {} } },
    {
      name: 'b',
      dependencies: {
        all: { a: '^1.0.0', c: '^2.0.0' }, unsatisfied: { c: '' },
      },
    },
  ])
  const wrapper = mount(Plugins)
  await flushPromises()

  expect(wrapper.text()).toContain('admin.noDependencies')
  expect(wrapper.find('span.badge.bg-green').text()).toBe('a: ^1.0.0')
  expect(wrapper.find('span.badge.bg-red').text()).toBe('c: ^2.0.0')
})

test('render operation buttons', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { all: {}, unsatisfied: {} }, enabled: true, config: true,
    },
    {
      name: 'b', dependencies: { all: {}, unsatisfied: {} }, enabled: true, config: false,
    },
    {
      name: 'c', dependencies: { all: {}, unsatisfied: {} }, enabled: false,
    },
  ])
  const wrapper = mount(Plugins)
  await flushPromises()
  const tbody = wrapper.find('tbody')

  expect(tbody.find('tr:nth-child(1)').text()).toContain('admin.disablePlugin')
  expect(tbody.find('tr:nth-child(1)').text()).toContain('admin.configurePlugin')
  expect(tbody.find('tr:nth-child(2)').text()).not.toContain('admin.configurePlugin')
  expect(tbody.find('tr:nth-child(3)').text()).toContain('admin.enablePlugin')
  expect(tbody.find('tr:nth-child(3)').text()).toContain('admin.deletePlugin')
})

test('enable plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { all: {}, unsatisfied: {} }, enabled: false,
    },
    {
      name: 'b', dependencies: { all: { c: '' }, unsatisfied: {} }, enabled: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({
      code: 1, message: '1', data: { reason: ['abc'] },
    })
    .mockResolvedValue({ code: 0, message: '0' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: '' })
  const wrapper = mount(Plugins)
  await flushPromises()

  wrapper
    .findAll('.actions')
    .at(0)
    .find('a')
    .trigger('click')
  await flushPromises()
  expect(showModal).toBeCalledWith({
    text: 'admin.noDependenciesNotice',
    okButtonType: 'warning',
  })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper
    .findAll('.actions')
    .at(0)
    .find('a')
    .trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'enable', name: 'a' },
  )
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    dangerousHTML: expect.stringContaining('<li>abc</li>'),
  })

  wrapper
    .findAll('.actions')
    .at(1)
    .find('a')
    .trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('admin.disablePlugin')
})

test('disable plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { all: {}, unsatisfied: {} }, enabled: true, config: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  const wrapper = mount(Plugins)
  await flushPromises()
  const button = wrapper.find('.actions').find('a')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'disable', name: 'a' },
  )
  button.trigger('click')
  await flushPromises()
  expect(toast.success).toBeCalledWith('0')
  expect(wrapper.text()).toContain('admin.enablePlugin')
})

test('delete plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a',
      title: 'My Plugin',
      dependencies: { all: {}, unsatisfied: {} },
      enabled: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: '' })
  const wrapper = mount(Plugins)
  await flushPromises()
  const button = wrapper.find('.actions').findAll('a')
    .at(1)

  button.trigger('click')
  await flushPromises()
  expect(showModal).toBeCalledWith({
    title: 'My Plugin',
    text: 'admin.confirmDeletion',
    okButtonType: 'danger',
  })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'delete', name: 'a' },
  )
  expect(toast.error).toBeCalledWith('1')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('No data')
})
