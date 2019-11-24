import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { MessageBoxData } from 'element-ui/types/message-box'
import { flushPromises } from '../../utils'
import Players from '@/views/user/Players.vue'

window.blessing.extra = {
  rule: 'rule',
  length: 'length',
}

test('display player name constraints', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [] })
  const wrapper = mount(Players)
  const text = wrapper.text()
  expect(text).toContain('rule')
  expect(text).toContain('length')
})

test('fetch players data before mount', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce({ data: [] })
    .mockResolvedValueOnce({
      data: [{
        pid: 1, tid_skin: 1, tid_cape: 0,
      }],
    })
    .mockResolvedValueOnce({ data: {} })
  mount(Players)
  expect(Vue.prototype.$http.get).toBeCalledWith('/user/player/list')

  mount(Players)
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/1')
})

test('click to preview player', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce({
      data: [
        {
          pid: 1, tid_skin: 1, tid_cape: 3,
        },
        {
          pid: 2, tid_skin: 0, tid_cape: 0,
        },
        {
          pid: 3, tid_skin: 2, tid_cape: 0,
        },
        {
          pid: 4, tid_skin: 0, tid_cape: 5,
        },
      ],
    })
    .mockResolvedValueOnce({ data: { hash: 'a' } })
    .mockResolvedValueOnce({ data: { hash: 'b' } })
    .mockResolvedValueOnce({ data: { hash: 'c' } })
    .mockResolvedValueOnce({ data: { hash: 'd' } })
  const wrapper = mount(Players)
  await flushPromises()

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

  wrapper.find('[data-test="to2d"]').trigger('click')
  expect(wrapper.find('img').attributes('src')).toBe('/preview/64/2.png')
  wrapper.find('tbody > tr:nth-child(4)').trigger('click')
  await flushPromises()
  expect(wrapper.find('img').attributes('src')).toBe('/preview/64/5.png')
})

test('change player name', async () => {
  Vue.prototype.$http.get
    .mockResolvedValue({
      data: [
        { pid: 1, name: 'old' },
      ],
    })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1 })
    .mockResolvedValue({ code: 0 })
  Vue.prototype.$prompt.mockImplementationOnce(() => Promise.reject('cancel'))
    .mockImplementation((_, { inputValidator }) => {
      if (inputValidator) {
        inputValidator('')
        inputValidator('new-name')
      }
      return Promise.resolve({ value: 'new-name' } as MessageBoxData)
    })
  const wrapper = mount(Players)
  await flushPromises()
  const button = wrapper.find('.btn-default')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/rename/1',
    { name: 'new-name' }
  )
  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('new-name')
})

test('delete player', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce({
      data: [
        { pid: 1, name: 'to-be-deleted' },
      ],
    })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1 })
    .mockResolvedValue({ code: 0 })
  Vue.prototype.$confirm
    .mockRejectedValueOnce({})
    .mockResolvedValue('confirm')
  const wrapper = mount(Players)
  await flushPromises()
  const button = wrapper.findAll('.btn-danger')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith('/user/player/delete/1')
  expect(wrapper.text()).toContain('to-be-deleted')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).not.toContain('to-be-deleted')
})

test('toggle preview mode', () => {
  Vue.prototype.$http.get.mockResolvedValueOnce({ data: [] })
  const wrapper = mount(Players)
  wrapper.find('[data-test="to2d"]').trigger('click')
  expect(wrapper.text()).toContain('user.player.texture-empty')
})

test('add player', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  Vue.prototype.$http.get.mockResolvedValueOnce({ data: [] })
  Vue.prototype.$http.post.mockResolvedValue({ code: 0 })
  const wrapper = mount(Players)
  const button = wrapper.find('[data-test=addPlayer]')

  wrapper.find('input[type="text"]').setValue('the-new')
  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledTimes(2)
})

test('clear texture', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  Vue.prototype.$http.get
    .mockResolvedValueOnce({
      data: [
        {
          pid: 1, tid_skin: 1, tid_cape: 0,
        },
      ],
    })
    .mockResolvedValueOnce({ data: {} })
    .mockResolvedValueOnce({ data: {} })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1 })
    .mockResolvedValue({ code: 0, message: 'ok' })
  const wrapper = mount(Players)
  await flushPromises()
  const button = wrapper.find('[data-test=clearTexture]')
  wrapper.find('.player').trigger('click')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.findAll('input[type="checkbox"]').at(0)
    .setChecked()
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/texture/clear/1',
    {
      skin: true, cape: false,
    }
  )
  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})
