import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import '@/scripts/i18n'
import Modal from '@/components/Modal.vue'
import { showModal, toast } from '@/scripts/notify'
import Users from '@/views/admin/Users.vue'

jest.mock('@/scripts/i18n', () => ({
  trans: (key: string) => key,
}))
jest.mock('@/scripts/notify')

test('fetch data after initializing', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [] })
  mount(Users)
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/user-data',
    {
      page: 1, perPage: 10, search: '', sortField: 'uid', sortType: 'asc',
    },
  )
})

test('humanize permission', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, permission: -1 },
      { uid: 2, permission: 0 },
      { uid: 3, permission: 1 },
      { uid: 4, permission: 2 },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  const text = wrapper.find('.vgt-table').text()
  expect(text).toContain('admin.banned')
  expect(text).toContain('admin.normal')
  expect(text).toContain('admin.admin')
  expect(text).toContain('admin.superAdmin')
})

test('generate players page link', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, permission: 0 },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-toggle="tooltip"]').attributes('href')).toBe('/admin/players?uid=1')
})

test('permission option should not be displayed for super admins', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, permission: 2 },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test=permission]').exists()).toBeFalse()
})

test('permission option should be displayed for admin as super admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 1, operations: 2,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test=permission]').exists()).toBeTrue()
})

test('permission option should be displayed for normal users as super admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 0, operations: 2,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test=permission]').exists()).toBeTrue()
})

test('permission option should be displayed for banned users as super admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: -1, operations: 2,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test=permission]').exists()).toBeTrue()
})

test('permission option should not be displayed for other admins as admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 1, operations: 1,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test=permission]').exists()).toBeFalse()
})

test('permission option should be displayed for normal users as admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 0, operations: 1,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test=permission]').exists()).toBeTrue()
})

test('permission option should be displayed for banned users as admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: -1, operations: 1,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test=permission]').exists()).toBeTrue()
})

test('deletion button should not be displayed for super admins', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, permission: 2 },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test="deleteUser"]').attributes('disabled')).toBe('disabled')
})

test('deletion button should be displayed for admins as super admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 1, operations: 2,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test="deleteUser"]').attributes('disabled')).toBeNil()
})

test('deletion button should be displayed for normal users as super admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 0, operations: 2,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test="deleteUser"]').attributes('disabled')).toBeNil()
})

test('deletion button should be displayed for banned users as super admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: -1, operations: 2,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test="deleteUser"]').attributes('disabled')).toBeNil()
})

test('deletion button should not be displayed for other admins as admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 1, operations: 1,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test="deleteUser"]').attributes('disabled')).toBe('disabled')
})

test('deletion button should be displayed for normal users as admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 0, operations: 1,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test="deleteUser"]').attributes('disabled')).toBeNil()
})

test('deletion button should be displayed for banned users as admin', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: -1, operations: 1,
      },
    ],
  })
  const wrapper = mount(Users)
  await flushPromises()
  expect(wrapper.find('[data-test="deleteUser"]').attributes('disabled')).toBeNil()
})

test('change email', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, email: 'a@b.c' },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: 'd@e.f' })
  const wrapper = mount(Users)
  await flushPromises()
  const button = wrapper.find('[data-test="email"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=email',
    { uid: 1, email: 'd@e.f' },
  )
  expect(wrapper.text()).toContain('a@b.c')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('d@e.f')
})

test('toggle verification', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, verified: false },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })

  const wrapper = mount(Users)
  await flushPromises()
  const button = wrapper.find('[data-test="verification"')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=verification',
    { uid: 1 },
  )
  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('admin.verified')
})

test('change nickname', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, nickname: 'old' },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: 'new' })
  const wrapper = mount(Users)
  await flushPromises()
  const button = wrapper.find('[data-test="nickname"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=nickname',
    { uid: 1, nickname: 'new' },
  )
  expect(wrapper.text()).toContain('old')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('new')
})

test('change password', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1 },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 0, message: '0' })
    .mockResolvedValueOnce({ code: 1, message: '1' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: 'password' })

  const wrapper = mount(Users)
  await flushPromises()
  const button = wrapper.find('.btn-default')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=password',
    { uid: 1, password: 'password' },
  )
  await flushPromises()
  expect(toast.success).toBeCalledWith('0')


  button.trigger('click')
  await flushPromises()
  expect(toast.error).toBeCalledWith('1')
})

test('change score', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, score: 23 },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({ code: 0, message: '0' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: '45' })

  const wrapper = mount(Users)
  await flushPromises()
  const button = wrapper.find('[data-test="score"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=score',
    { uid: 1, score: 45 },
  )
  expect(wrapper.text()).toContain('23')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('45')
})

test('change permission', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      {
        uid: 1, permission: 0, operations: 2,
      },
      {
        uid: 2, permission: 0, operations: 1,
      },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })

  const wrapper = mount(Users)
  await flushPromises()

  wrapper
    .findAll('[data-test=permission]')
    .at(0)
    .trigger('click')
  expect(wrapper.findAll('[type=radio]')).toHaveLength(3)

  wrapper
    .findAll('[data-test=permission]')
    .at(1)
    .trigger('click')
  expect(wrapper.findAll('[type=radio]')).toHaveLength(2)

  const button = wrapper.findAll('[data-test=permission]').at(1)
  button.trigger('click')
  wrapper.find('[type=radio]:nth-child(1)').setChecked()
  wrapper.find(Modal).vm.$emit('confirm')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=permission',
    { uid: 2, permission: -1 },
  )

  button.trigger('click')
  wrapper.find('[type=radio]:nth-child(1)').setChecked()
  wrapper.find(Modal).vm.$emit('confirm')
  await flushPromises()
  expect(wrapper.text()).toContain('admin.banned')
})

test('delete user', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, nickname: 'to-be-deleted' },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  showModal
    .mockRejectedValueOnce(null)
    .mockResolvedValue({ value: '' })

  const wrapper = mount(Users)
  await flushPromises()
  const button = wrapper.find('.btn-danger')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=delete',
    { uid: 1 },
  )
  expect(wrapper.text()).toContain('to-be-deleted')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('No data')
})
