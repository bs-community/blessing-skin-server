import 'bootstrap'
import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../utils'
import { trans } from '@/scripts/i18n'
import { toast } from '@/scripts/notify'
import ApplyToPlayerDialog from '@/components/ApplyToPlayerDialog.vue'

jest.mock('@/scripts/notify')

test('submit applying texture', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [{ pid: 1, name: 'a' }] })
  Vue.prototype.$http.post.mockResolvedValueOnce({ code: 1 })
    .mockResolvedValue({ code: 0, message: 'ok' })
  const wrapper = mount<Vue & { fetchList(): Promise<void> }>(ApplyToPlayerDialog)
  await wrapper.vm.fetchList()
  const button = wrapper.find('.btn-outline-info')

  button.trigger('click')
  expect(toast.info).toBeCalledWith(trans('user.emptySelectedTexture'))

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
  expect(avatarUrl({ tid_skin: 1 })).toBe('/avatar/1?3d&size=45')
})

test('search players', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [{ pid: 1, name: 'abc' }] })
  const wrapper = mount<Vue & { fetchList(): Promise<void> }>(ApplyToPlayerDialog)
  await wrapper.vm.fetchList()

  wrapper.find('input').setValue('e')
  expect(wrapper.find('.btn-outline-info').exists()).toBeFalse()

  wrapper.find('input').setValue('b')
  expect(wrapper.find('.btn-outline-info').exists()).toBeTrue()
})
