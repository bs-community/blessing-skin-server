import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import Players from '@/components/user/Players.vue'
import { swal } from '@/js/notify'

jest.mock('toastr')
jest.mock('@/js/notify')

window.blessing.extra = {
  rule: 'rule',
  length: 'length',
}

test('display player name constraints', () => {
  const wrapper = mount(Players)
  const text = wrapper.text()
  expect(text).toContain('rule')
  expect(text).toContain('length')
})

test('fetch players data before mount', () => {
  Vue.prototype.$http.get.mockResolvedValue([])
  mount(Players)
  expect(Vue.prototype.$http.get).toBeCalledWith('/user/player/list')
})

test('click to preview player', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce([
      {
        pid: 1, tid_skin: 1, tid_cape: 3,
      },
      {
        pid: 2, tid_skin: 0, tid_cape: 0,
      },
      {
        pid: 3, tid_skin: 2, tid_cape: 0,
      },
    ])
    .mockResolvedValueOnce({ hash: 'a' })
    .mockResolvedValueOnce({ hash: 'b' })
    .mockResolvedValueOnce({ hash: 'c' })
  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()

  wrapper.find('tbody > tr:nth-child(1)').trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/1')
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/3')
  expect(wrapper.findAll('.player').at(0)
    .classes('player-selected')).toBeTrue()

  wrapper.find('tbody > tr:nth-child(2)').trigger('click')
  await flushPromises()

  wrapper.find('tbody > tr:nth-child(3)').trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/2')
})

test('change player name', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce([
      { pid: 1, name: 'old' },
    ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1 })
    .mockResolvedValue({ errno: 0 })
  swal.mockImplementationOnce(() => Promise.resolve({ dismiss: 1 }))
    .mockImplementation(({ inputValidator }) => {
      if (inputValidator) {
        inputValidator('')
        inputValidator('new-name')
      }
      return Promise.resolve({ value: 'new-name' })
    })
  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('.btn-default')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/rename',
    { pid: 1, new_player_name: 'new-name' }
  )
  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('new-name')
})

test('load iCheck', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce([
      { pid: 1 },
    ])
  window.$ = jest.fn(() => ({
    iCheck: () => ({
      on(_: Event, cb: CallableFunction) {
        cb()
      },
    }),
    0: {
      dispatchEvent: () => {},
    },
  }))
  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  wrapper.find('.btn-warning').trigger('click')
  expect(window.$).toBeCalled()
})

test('delete player', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce([
      { pid: 1, name: 'to-be-deleted' },
    ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1 })
    .mockResolvedValue({ errno: 0 })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValue({})
  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('.btn-danger')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  expect(wrapper.text()).toContain('to-be-deleted')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).not.toContain('to-be-deleted')
})

test('toggle preview mode', () => {
  Vue.prototype.$http.get.mockResolvedValueOnce([])
  const wrapper = mount(Players)
  wrapper.find('[data-test="to2d"]').trigger('click')
  expect(wrapper.text()).toContain('user.player.texture-empty')
})

test('add player', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  Vue.prototype.$http.get.mockResolvedValueOnce([])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1 })
    .mockResolvedValue({ errno: 0 })
  const wrapper = mount(Players)
  const button = wrapper.findAll('.modal-footer').at(0)
    .find('a')
  wrapper.find('input[type="text"]').setValue('the-new')

  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/add',
    { player_name: 'the-new' }
  )
  await flushPromises()
  await wrapper.vm.$nextTick()
  expect(wrapper.text()).not.toContain('the-new')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledTimes(2)
})

test('clear texture', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  Vue.prototype.$http.get.mockResolvedValueOnce([
    {
      pid: 1, tid_skin: 1, tid_cape: 0,
    },
  ])
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1 })
    .mockResolvedValue({ errno: 0, msg: 'ok' })
  const wrapper = mount(Players)
  await wrapper.vm.$nextTick()
  const button = wrapper.findAll('.modal-footer').at(1)
    .find('a')
  wrapper.find('.player').trigger('click')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.findAll('input[type="checkbox"]').at(0)
    .setChecked()
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/texture/clear',
    {
      pid: 1, skin: true, cape: false,
    }
  )
  button.trigger('click')
  await flushPromises()
  expect(swal).toBeCalledWith({ type: 'success', text: 'ok' })
})
