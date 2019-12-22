import 'bootstrap'
import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../utils'
import { toast } from '@/scripts/notify'
import ApplyToPlayerDialog from '@/components/ApplyToPlayerDialog.vue'

jest.mock('@/scripts/notify')

test('submit applying texture', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [{ pid: 1 }] })
  Vue.prototype.$http.post.mockResolvedValueOnce({ code: 1 })
    .mockResolvedValue({ code: 0, message: 'ok' })
  const wrapper = mount(ApplyToPlayerDialog)
  const button = wrapper.find('[data-test=submit]')

  button.trigger('click')
  expect(toast.info).toBeCalledWith('user.emptySelectedPlayer')

  wrapper.setData({ selected: 1 })
  button.trigger('click')
  expect(toast.info).toBeCalledWith('user.emptySelectedTexture')

  wrapper.setProps({ skin: 1 })
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/set/1',
    {
      skin: 1,
      cape: undefined,
    },
  )
  wrapper.setProps({ skin: 0, cape: 1 })
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/set/1',
    {
      skin: undefined,
      cape: 1,
    },
  )
  await flushPromises()
  expect(toast.success).toBeCalledWith('ok')
})

test('compute avatar URL', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  // eslint-disable-next-line camelcase
  const wrapper = mount<Vue & { avatarUrl(player: { tid_skin: number }): string }>(ApplyToPlayerDialog)
  const { avatarUrl } = wrapper.vm
  expect(avatarUrl({ tid_skin: 1 })).toBe('/avatar/1/35')
})
