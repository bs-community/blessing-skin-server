import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { walkFetch } from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import Modal from '@/components/Modal.vue'
import OAuth from '@/views/user/OAuth.vue'

jest.mock('@/scripts/notify')

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
  Vue.prototype.$http.get.mockResolvedValue([])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ message: 'fail' })
    .mockResolvedValueOnce({ id: 1, name: 'name' })
  const wrapper = mount(OAuth)
  await flushPromises()

  const modal = wrapper.find(Modal)
  const inputs = wrapper.findAll('.value')
  inputs.at(0).find('input')
    .setValue('name')
  inputs.at(1).find('input')
    .setValue('https://example.com/')

  modal.vm.$emit('confirm')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/oauth/clients',
    { name: 'name', redirect: 'https://example.com/' },
  )
  expect(toast.error).toBeCalledWith('fail')

  modal.vm.$emit('confirm')
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
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: 'new-name' })
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
    }),
  )
  expect(toast.error).toBeCalledWith('fail')

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
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: 'https://example.net/' })
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
    }),
  )
  expect(toast.error).toBeCalledWith('fail')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('https://example.net/')
})

test('remove app', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    { id: 1, name: 'name' },
  ])
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: '' })

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
