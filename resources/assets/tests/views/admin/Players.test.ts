import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { MessageBoxData } from 'element-ui/types/message-box'
import { flushPromises } from '../../utils'
import Players from '@/views/admin/Players.vue'

test('fetch data after initializing', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [] })
  mount(Players)
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/player-data',
    {
      page: 1, perPage: 10, search: '', sortField: 'pid', sortType: 'asc',
    },
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
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })

  const wrapper = mount(Players)
  await flushPromises()
  const button = wrapper.find('[data-test=changeTexture]')
  wrapper.findAll('.btn-default').trigger('click')

  wrapper.find('.modal-body input').setValue('5')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=texture',
    {
      pid: 1, tid: 5, type: 'skin',
    },
  )
  button.trigger('click')
  await flushPromises()
  expect(wrapper.html()).toContain('/preview/64/5.png')
  expect($).toBeCalledWith('.modal')
})

test('change player name', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { pid: 1, name: 'old' },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  Vue.prototype.$prompt
    .mockImplementationOnce(() => Promise.reject())
    .mockImplementation((_, options) => {
      if (options.inputValidator) {
        options.inputValidator('')
        options.inputValidator('new')
      }
      return Promise.resolve({ value: 'new' } as MessageBoxData)
    })
  const wrapper = mount(Players)
  await flushPromises()
  const button = wrapper.find('[data-test="name"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=name',
    { pid: 1, name: 'new' },
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
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  Vue.prototype.$prompt
    .mockRejectedValueOnce('')
    .mockResolvedValue({ value: '3' } as MessageBoxData)

  const wrapper = mount(Players)
  await flushPromises()
  const button = wrapper.find('[data-test="owner"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=owner',
    { pid: 1, uid: '3' },
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
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce('')
    .mockResolvedValue('confirm')

  const wrapper = mount(Players)
  await flushPromises()
  const button = wrapper.find('.btn-danger')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/players?action=delete',
    { pid: 1 },
  )
  expect(wrapper.text()).toContain('to-be-deleted')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('No data')
})
