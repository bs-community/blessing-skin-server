import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Plugins from '@/components/admin/Plugins.vue'
import toastr from 'toastr'
import { flushPromises } from '../../utils'
import { swal } from '@/js/notify'

jest.mock('@/js/notify')

test('render dependencies', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { name: 'a', dependencies: { requirements: [] } },
    {
      name: 'b',
      dependencies: {
        requirements: { a: '^1.0.0', c: '^2.0.0' }, unsatisfiedRequirements: { c: {} },
      },
    },
  ])
  const wrapper = mount(Plugins)
  await flushPromises()

  expect(wrapper.text()).toContain('admin.noDependencies')
  expect(wrapper.find('span.label.bg-green').text()).toBe('a: ^1.0.0')
  expect(wrapper.find('span.label.bg-red').text()).toBe('c: ^2.0.0')
})

test('render operation buttons', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { requirements: [] }, enabled: true, config: true,
    },
    {
      name: 'b', dependencies: { requirements: [] }, enabled: true, config: false,
    },
    {
      name: 'c', dependencies: { requirements: [] }, enabled: false,
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
      name: 'a', dependencies: { requirements: [] }, enabled: false,
    },
    {
      name: 'b', dependencies: { requirements: {} }, enabled: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({
      errno: 1, msg: '1', reason: ['`a<div></div>`b'],
    })
    .mockResolvedValue({ errno: 0, msg: '0' })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValueOnce({})
  const wrapper = mount(Plugins)
  await flushPromises()

  wrapper.findAll('.actions').at(0)
    .find('a')
    .trigger('click')
  await flushPromises()
  expect(swal).toBeCalledWith({
    text: 'admin.noDependenciesNotice',
    type: 'warning',
    showCancelButton: true,
  })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.findAll('.actions').at(0)
    .find('a')
    .trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'enable', name: 'a' }
  )
  expect(swal).toBeCalledWith({
    type: 'warning',
    html: '<p>1</p><ul><li>`a&lt;div&gt;&lt;/div&gt;`b</li></ul>',
  })

  wrapper.findAll('.actions').at(1)
    .find('a')
    .trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('admin.disablePlugin')
})

test('disable plugin', async () => {
  jest.spyOn(toastr, 'success')
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { requirements: [] }, enabled: true, config: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValue({ errno: 0, msg: '0' })
  const wrapper = mount(Plugins)
  await flushPromises()
  const button = wrapper.find('.actions').find('a')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'disable', name: 'a' }
  )
  button.trigger('click')
  await flushPromises()
  expect(toastr.success).toBeCalledWith('0')
  expect(wrapper.text()).toContain('admin.enablePlugin')
})

test('delete plugin', async () => {
  jest.spyOn(toastr, 'success')
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { requirements: [] }, enabled: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValue({ errno: 0, msg: '0' })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValue({})
  const wrapper = mount(Plugins)
  await flushPromises()
  const button = wrapper.find('.actions').findAll('a')
    .at(1)

  button.trigger('click')
  await flushPromises()
  expect(swal).toBeCalledWith({
    text: 'admin.confirmDeletion',
    type: 'warning',
    showCancelButton: true,
  })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'delete', name: 'a' }
  )
  expect(swal).toBeCalledWith({ type: 'warning', text: '1' })

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('No data')
})
