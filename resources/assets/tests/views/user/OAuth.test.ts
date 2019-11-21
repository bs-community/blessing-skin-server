import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { MessageBoxData } from 'element-ui/types/message-box'
import { flushPromises } from '../../utils'
import { walkFetch } from '@/scripts/net'
import OAuth from '@/views/user/OAuth.vue'

jest.mock('@/scripts/net', () => ({
  walkFetch: jest.fn(),
  init: {},
}))

test('basic render', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { id: 1 },
  ])
  const wrapper = mount(OAuth)
  await flushPromises()
  expect(wrapper.findAll('[data-test=remove]')).toHaveLength(1)
})

test('create app', async () => {
  Object.assign(window, { $: () => ({ modal() {} }) })
  Vue.prototype.$http.get.mockResolvedValue([])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ message: 'fail' })
    .mockResolvedValueOnce({ id: 1, name: 'name' })
  const wrapper = mount(OAuth)
  await flushPromises()

  const button = wrapper.find('[data-test=create]')
  const inputs = wrapper.findAll('.value')
  inputs.at(0).find('input')
    .setValue('name')
  inputs.at(1).find('input')
    .setValue('https://example.com/')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/oauth/clients',
    { name: 'name', redirect: 'https://example.com/' }
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('fail')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('name')
})

test('modify name', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { id: 1, name: 'old' },
  ])
  walkFetch
    .mockResolvedValueOnce({ message: 'fail' })
    .mockResolvedValueOnce({ id: 1, name: 'new-name' })
  Vue.prototype.$prompt
    .mockRejectedValueOnce('')
    .mockResolvedValue({ value: 'new-name' } as MessageBoxData)
  const wrapper = mount(OAuth)
  await flushPromises()
  const button = wrapper.find('[data-test=name]')

  button.trigger('click')
  await flushPromises()
  expect(walkFetch).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(walkFetch).toBeCalledWith(
    expect.objectContaining({
      url: '/oauth/clients/1',
      body: JSON.stringify({ name: 'new-name' }),
      method: 'PUT',
    })
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('fail')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('new-name')
})

test('modify redirect', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { id: 1, redirect: 'https://example.com/' },
  ])
  walkFetch
    .mockResolvedValueOnce({ message: 'fail' })
    .mockResolvedValueOnce({ id: 1, redirect: 'https://example.net/' })
  Vue.prototype.$prompt
    .mockRejectedValueOnce('')
    .mockResolvedValue({ value: 'https://example.net/' } as MessageBoxData)
  const wrapper = mount(OAuth)
  await flushPromises()
  const button = wrapper.find('[data-test=callback]')

  button.trigger('click')
  await flushPromises()
  expect(walkFetch).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(walkFetch).toBeCalledWith(
    expect.objectContaining({
      url: '/oauth/clients/1',
      body: JSON.stringify({ redirect: 'https://example.net/' }),
      method: 'PUT',
    })
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('fail')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('https://example.net/')
})

test('remove app', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { id: 1, name: 'name' },
  ])
  Vue.prototype.$confirm
    .mockRejectedValueOnce('cancel')
    .mockResolvedValue('confirm')

  const wrapper = mount(OAuth)
  await flushPromises()
  const button = wrapper.find('[data-test=remove]')

  button.trigger('click')
  await flushPromises()
  expect(walkFetch).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('No data')
})
