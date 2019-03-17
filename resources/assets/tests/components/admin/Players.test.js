import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import Players from '@/components/admin/Players'
import { swal } from '@/js/notify'

jest.mock('@/js/notify')

test('fetch data after initializing', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [] })
  mount(Players)
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/player-data',
    {
      page: 1, perPage: 10, search: '', sortField: 'pid', sortType: 'asc',
    }
  )
})

test('update tables', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: Array.from({ length: 20 }).map((item, pid) => ({ pid })),
  })
  const wrapper = mount(Players)

  wrapper.find('.vgt-input').setValue('abc')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/player-data',
    {
      page: 1, perPage: 10, search: 'abc', sortField: 'pid', sortType: 'asc',
    }
  )
  wrapper.vm.onPageChange({ currentPage: 2 })
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/player-data',
    {
      page: 2, perPage: 10, search: 'abc', sortField: 'pid', sortType: 'asc',
    }
  )
  wrapper.vm.onPerPageChange({ currentPerPage: 5 })
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/player-data',
    {
      page: 2, perPage: 5, search: 'abc', sortField: 'pid', sortType: 'asc',
    }
  )
  wrapper.vm.onSortChange({ sortType: 'desc', columnIndex: 0 })
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/player-data',
    {
      page: 2, perPage: 5, search: 'abc', sortField: 'pid', sortType: 'desc',
    }
  )
})

test('change texture', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { pid: 1, tid_skin: 0 },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })

  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('.btn-primary')
  wrapper.find('.btn-default').trigger('click')

  wrapper.find('.modal-body').find('input')
    .setValue('5')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=texture',
    {
      pid: 1, tid: 5, type: 'skin',
    }
  )
  button.trigger('click')
  await flushPromises()
  expect(wrapper.html()).toContain('/preview/64/5.png')
  expect(window.$).toBeCalledWith('.modal')
})

test('change player name', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { pid: 1, name: 'old' },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  swal.mockImplementationOnce(() => ({ dismiss: 1 }))
    .mockImplementation(options => {
      options.inputValidator()
      options.inputValidator('new')
      return { value: 'new' }
    })
  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('[data-test="name"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=name',
    { pid: 1, name: 'new' }
  )
  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('new')
})

test('change owner', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { pid: 1, uid: 2 },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValue({ value: '3' })

  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('[data-test="owner"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=owner',
    { pid: 1, uid: '3' }
  )
  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('3')
})

test('delete player', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { pid: 1, name: 'to-be-deleted' },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValue({})

  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('.btn-danger')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=delete',
    { pid: 1 }
  )
  expect(wrapper.text()).toContain('to-be-deleted')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('No data')
})
