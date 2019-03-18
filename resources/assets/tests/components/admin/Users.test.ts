import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Users from '@/components/admin/Users.vue'
import toastr from 'toastr'
import { swal } from '@/js/notify'
import '@/js/i18n'
import { flushPromises } from '../../utils'

jest.mock('@/js/notify')
jest.mock('@/js/i18n', () => ({
  trans: (key: string) => key,
}))

test('fetch data after initializing', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [] })
  mount(Users)
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/user-data',
    {
      page: 1, perPage: 10, search: '', sortField: 'uid', sortType: 'asc',
    }
  )
})

test('update tables', () => {
  interface Methods {
    onPageChange(options: { currentPage: number }): void
    onPerPageChange(options: { currentPerPage: number }): void
    onSortChange(options: { sortType: 'asc' | 'desc', columnIndex: number }): void
  }

  Vue.prototype.$http.get.mockResolvedValue({
    data: Array.from({ length: 20 }).map((_, uid) => ({ uid })),
  })
  const wrapper = mount<Vue & Methods>(Users)

  wrapper.find('.vgt-input').setValue('abc')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/user-data',
    {
      page: 1, perPage: 10, search: 'abc', sortField: 'uid', sortType: 'asc',
    }
  )
  wrapper.vm.onPageChange({ currentPage: 2 })
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/user-data',
    {
      page: 2, perPage: 10, search: 'abc', sortField: 'uid', sortType: 'asc',
    }
  )
  wrapper.vm.onPerPageChange({ currentPerPage: 5 })
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/user-data',
    {
      page: 2, perPage: 5, search: 'abc', sortField: 'uid', sortType: 'asc',
    }
  )
  wrapper.vm.onSortChange({ sortType: 'desc', columnIndex: 0 })
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/admin/user-data',
    {
      page: 2, perPage: 5, search: 'abc', sortField: 'uid', sortType: 'desc',
    }
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
  await wrapper.vm.$nextTick()
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('[data-toggle="tooltip"]').attributes('href')).toBe('/admin/players?uid=1')
})

test('permission option should not be displayed for super admins', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, permission: 2 },
    ],
  })
  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
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
  await wrapper.vm.$nextTick()
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
  await wrapper.vm.$nextTick()
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
  await wrapper.vm.$nextTick()
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
  await wrapper.vm.$nextTick()
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
  await wrapper.vm.$nextTick()
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('[data-test=permission]').exists()).toBeTrue()
})

test('deletion button should not be displayed for super admins', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, permission: 2 },
    ],
  })
  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  expect(wrapper.find('.btn-danger').attributes('disabled')).toBe('disabled')
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('.btn-danger').attributes('disabled')).toBeNil()
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('.btn-danger').attributes('disabled')).toBeNil()
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('.btn-danger').attributes('disabled')).toBeNil()
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('.btn-danger').attributes('disabled')).toBe('disabled')
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('.btn-danger').attributes('disabled')).toBeNil()
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
  await wrapper.vm.$nextTick()
  expect(wrapper.find('.btn-danger').attributes('disabled')).toBeNil()
})

test('change email', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, email: 'a@b.c' },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  swal.mockImplementationOnce(() => Promise.resolve({ dismiss: 1 }))
    .mockImplementation(options => {
      if (options.inputValidator) {
        options.inputValidator('')
        options.inputValidator('value')
      }
      return Promise.resolve({ value: 'd@e.f' })
    })
  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('[data-test="email"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=email',
    { uid: 1, email: 'd@e.f' }
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
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })

  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('[data-test="verification"')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=verification',
    { uid: 1 }
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
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  swal.mockImplementationOnce(() => Promise.resolve({ dismiss: 1 }))
    .mockImplementation(options => {
      if (options.inputValidator) {
        options.inputValidator('')
        options.inputValidator('value')
      }
      return Promise.resolve({ value: 'new' })
    })
  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('[data-test="nickname"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=nickname',
    { uid: 1, nickname: 'new' }
  )
  expect(wrapper.text()).toContain('old')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('new')
})

test('change password', async () => {
  jest.spyOn(toastr, 'success')
  jest.spyOn(toastr, 'warning')
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1 },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValue({ value: 'password' })

  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('.btn-default')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=password',
    { uid: 1, password: 'password' }
  )
  await flushPromises()
  expect(toastr.success).toBeCalledWith('0')


  button.trigger('click')
  await flushPromises()
  expect(toastr.warning).toBeCalledWith('1')
})

test('change score', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: [
      { uid: 1, score: 23 },
    ],
  })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValueOnce({ errno: 0, msg: '0' })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValue({ value: '45' })

  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('[data-test="score"]')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=score',
    { uid: 1, score: 45 }
  )
  expect(wrapper.text()).toContain('23')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('45')
})

test('change permission', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce({
      data: [
        {
          uid: 1, permission: 0, operations: 2,
        },
      ],
    })
    .mockResolvedValueOnce({
      data: [
        {
          uid: 1, permission: 0, operations: 1,
        },
      ],
    })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValue({ errno: 0, msg: '0' })
  swal
    .mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValueOnce({ value: 1 })
    .mockResolvedValueOnce({ value: -1 })

  let wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  let button = wrapper.find('[data-test=permission]')

  button.trigger('click')
  expect(swal.mock.calls[0][0].inputOptions).toStrictEqual({
    '-1': 'admin.banned',
    0: 'admin.normal',
    1: 'admin.admin',
  })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=permission',
    { uid: 1, permission: 1 }
  )
  expect(wrapper.text()).toContain('admin.normal')

  wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  button = wrapper.find('[data-test=permission]')

  button.trigger('click')
  expect(swal.mock.calls[2][0].inputOptions).toStrictEqual({
    '-1': 'admin.banned',
    0: 'admin.normal',
  })
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
    .mockResolvedValueOnce({ errno: 1, msg: '1' })
    .mockResolvedValue({ errno: 0, msg: '0' })
  swal.mockResolvedValueOnce({ dismiss: 1 })
    .mockResolvedValue({})

  const wrapper = mount(Users)
  await wrapper.vm.$nextTick()
  const button = wrapper.find('.btn-danger')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/admin/users?action=delete',
    { uid: 1 }
  )
  expect(wrapper.text()).toContain('to-be-deleted')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.text()).toContain('No data')
})
