import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Market from '@/views/admin/Market.vue'
import { flushPromises } from '../../utils'

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
  const wrapper = mount(Market)
  await flushPromises()

  expect(wrapper.text()).toContain('admin.noDependencies')
  expect(wrapper.find('span.label.bg-green').text()).toBe('a: ^1.0.0')
  expect(wrapper.find('span.label.bg-red').text()).toBe('c: ^2.0.0')
})

test('render operation buttons', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { requirements: [] }, installed: true, update_available: true,
    },
    {
      name: 'b', dependencies: { requirements: [] }, installed: true, enabled: true,
    },
    {
      name: 'c', dependencies: { requirements: [] }, installed: true,
    },
    {
      name: 'd', dependencies: { requirements: [] }, installed: false,
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
      name: 'd', dependencies: { requirements: [] }, installed: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  const wrapper = mount(Market)
  await flushPromises()
  const button = wrapper.find('button')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/market/download',
    { name: 'd' }
  )
  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(wrapper.text()).toContain('admin.enablePlugin')
})

test('update plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a',
      version: '2.0.0',
      dependencies: { requirements: [] },
      installed: '1.0.0',
      update_available: true,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce('')
    .mockResolvedValue('confirm')
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
    { name: 'a' }
  )
})

test('enable installed plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', dependencies: { requirements: [] }, installed: true,
    },
    {
      name: 'b', dependencies: { requirements: {} }, installed: true,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({
      errno: 1, msg: '1', reason: ['`a<div></div>`b'],
    })
    .mockResolvedValue({ errno: 0, msg: '0' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce('')
    .mockResolvedValue('confirm')
  const wrapper = mount(Market)
  await flushPromises()
  const buttons = wrapper.findAll('button')

  buttons.at(0).trigger('click')
  await flushPromises()
  expect(Vue.prototype.$confirm).toBeCalledWith(
    'admin.noDependenciesNotice',
    { type: 'warning' }
  )
  expect(Vue.prototype.$http.post).not.toBeCalled()

  buttons.at(0).trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'enable', name: 'a' }
  )
  expect(Vue.prototype.$alert).toBeCalledWith(
    '<p>1</p><ul><li>`a&lt;div&gt;&lt;/div&gt;`b</li></ul>',
    {
      type: 'warning',
      dangerouslyUseHTMLString: true,
    }
  )

  buttons.at(1).trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('admin.statusEnabled')
})
