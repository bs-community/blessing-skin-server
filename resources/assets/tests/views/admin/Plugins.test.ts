import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { showModal, toast } from '@/scripts/notify'
import Plugins from '@/views/admin/Plugins.vue'

jest.mock('@/scripts/notify')

test('render config button', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', icon: {}, enabled: true, config: true,
    },
    {
      name: 'b', icon: {}, enabled: false, config: true,
    },
    {
      name: 'c', icon: {}, enabled: false, config: false,
    },
  ])
  const wrapper = mount(Plugins)
  await flushPromises()

  expect(wrapper.find('.info-box:nth-child(1) .fa-cog').exists()).toBeTrue()
  expect(wrapper.find('.info-box:nth-child(2) .fa-cog').exists()).toBeFalse()
  expect(wrapper.find('.info-box:nth-child(3) .fa-cog').exists()).toBeFalse()
})

test('enable plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', icon: {}, enabled: false,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({
      code: 1, message: '1', data: { reason: ['abc'] },
    })
    .mockResolvedValue({ code: 0, message: '0' })
  const wrapper = mount(Plugins)
  await flushPromises()
  const checkbox = wrapper.find('input[type=checkbox]')

  checkbox.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'enable', name: 'a' },
  )
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    dangerousHTML: expect.stringContaining('<li>abc</li>'),
  })

  checkbox.trigger('click')
  await flushPromises()
  expect(toast.success).toBeCalled()
})

test('disable plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a', icon: {}, enabled: true,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  const wrapper = mount(Plugins)
  await flushPromises()
  const checkbox = wrapper.find('input[type="checkbox"]')

  checkbox.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/plugins/manage',
    { action: 'disable', name: 'a' },
  )
  checkbox.trigger('click')
  await flushPromises()
  expect(toast.success).toBeCalledWith('0')
  expect(checkbox.attributes('checked')).toBeFalsy()
})

test('delete plugin', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a',
      title: 'My Plugin',
      icon: {},
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
  const button = wrapper.find('.plugin-actions a')

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
  expect(wrapper.text()).not.toContain('My Plugin')
})

test('readme link', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      name: 'a',
      readme: true,
      icon: {},
    },
  ])
  const wrapper = mount(Plugins)
  await flushPromises()

  const link = wrapper.find('.plugin-actions > a:nth-child(1)')
  expect(link.attributes('href')).toBe('/admin/plugins/readme/a')
})
