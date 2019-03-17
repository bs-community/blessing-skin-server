import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import ClosetItem from '@/components/user/ClosetItem'
import { swal } from '@/js/notify'

jest.mock('@/js/notify')

function factory(opt = {}) {
  return {
    tid: 1,
    name: 'texture',
    type: 'steve',
    ...opt,
  }
}

test('computed values', () => {
  const wrapper = mount(ClosetItem, { propsData: factory() })
  expect(wrapper.find('img').attributes('src')).toBe('/preview/1.png')
  expect(wrapper.find('a.more').attributes('href')).toBe('/skinlib/show/1')
})

test('selected item', () => {
  const wrapper = mount(ClosetItem, { propsData: factory({ selected: true }) })
  expect(wrapper.find('.item').classes('item-selected')).toBeTrue()
})

test('click item body', () => {
  const wrapper = mount(ClosetItem, { propsData: factory() })

  wrapper.find('.item').trigger('click')
  expect(wrapper.emitted().select).toBeUndefined()

  wrapper.find('.item-body').trigger('click')
  expect(wrapper.emitted().select).toBeTruthy()
})

test('rename texture', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 0 })
    .mockResolvedValueOnce({ errno: 1 })
  swal.mockImplementationOnce(() => ({ dismiss: 'cancel' }))
    .mockImplementation(options => {
      options.inputValidator('name')
      options.inputValidator()
      return { value: 'new-name' }
    })
  const wrapper = mount(ClosetItem, { propsData: factory() })
  const button = wrapper.findAll('.dropdown-menu > li').at(0)
    .find('a')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()

  button.trigger('click')
  await flushPromises()
  expect(wrapper.find('.texture-name > span').text()).toBe('new-name (steve)')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/closet/rename',
    { tid: 1, new_name: 'new-name' }
  )
})

test('remove texture', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 0 })
    .mockResolvedValueOnce({ errno: 1 })
  swal
    .mockResolvedValueOnce({ dismiss: 'cancel' })
    .mockResolvedValue({})

  const wrapper = mount(ClosetItem, { propsData: factory() })
  const button = wrapper.findAll('.dropdown-menu > li').at(1)
    .find('a')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()

  button.trigger('click')
  await flushPromises()
  expect(wrapper.emitted()['item-removed']).toBeTruthy()
  expect(Vue.prototype.$http.post).toBeCalledWith('/user/closet/remove', { tid: 1 })
})

test('set as avatar', async () => {
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 0 })
    .mockResolvedValueOnce({ errno: 1 })
  swal
    .mockResolvedValueOnce({ dismiss: 'cancel' })
    .mockResolvedValue({})
  window.$ = jest.fn(() => ({
    each(fn) {
      fn()
    },
    prop() {},
    attr() {
      return ''
    },
  }))

  const wrapper = mount(ClosetItem, { propsData: factory() })
  const button = wrapper.findAll('.dropdown-menu > li').at(2)
    .find('a')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()

  button.trigger('click')
  await flushPromises()
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith('/user/profile/avatar', { tid: 1 })
  expect(window.$).toBeCalledWith('[alt="User Image"]')
})

test('no avatar option if texture is cape', () => {
  const wrapper = mount(ClosetItem, { propsData: factory({ type: 'cape' }) })
  const button = wrapper.findAll('.dropdown-menu > li').at(2)
  expect(button.isEmpty()).toBeTrue()
})
