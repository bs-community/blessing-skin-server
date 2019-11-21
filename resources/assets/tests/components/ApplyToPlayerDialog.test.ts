import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../utils'
import ApplyToPlayerDialog from '@/components/ApplyToPlayerDialog.vue'

test('submit applying texture', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  Vue.prototype.$http.get.mockResolvedValue({ data: [{ pid: 1 }] })
  Vue.prototype.$http.post.mockResolvedValueOnce({ code: 1 })
    .mockResolvedValue({ code: 0, message: 'ok' })
  const wrapper = mount(ApplyToPlayerDialog)
  const button = wrapper.find('[data-test=submit]')

  button.trigger('click')
  expect(Vue.prototype.$message.info).toBeCalledWith('user.emptySelectedPlayer')

  wrapper.setData({ selected: 1 })
  button.trigger('click')
  expect(Vue.prototype.$message.info).toBeCalledWith('user.emptySelectedTexture')

  wrapper.setProps({ skin: 1 })
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/set/1',
    {
      skin: 1,
      cape: undefined,
    }
  )
  wrapper.setProps({ skin: 0, cape: 1 })
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/set/1',
    {
      skin: undefined,
      cape: 1,
    }
  )
  await flushPromises()
  expect(Vue.prototype.$message.success).toBeCalledWith('ok')
})

test('compute avatar URL', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  // eslint-disable-next-line camelcase
  const wrapper = mount<Vue & { avatarUrl(player: { tid_skin: number }): string }>(ApplyToPlayerDialog)
  const { avatarUrl } = wrapper.vm
  expect(avatarUrl({ tid_skin: 1 })).toBe('/avatar/35/1')
})
