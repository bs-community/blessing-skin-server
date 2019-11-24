import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Show from '@/views/skinlib/Show.vue'
import { MessageBoxData } from 'element-ui/types/message-box'
import { flushPromises } from '../../utils'

type Component = Vue & {
  liked: boolean
  likes: number
  public: boolean
  name: string
  type: 'steve' | 'alex' | 'cape'
}

window.blessing.extra = {
  download: true,
  currentUid: 0,
  admin: false,
  nickname: 'author',
  inCloset: false,
}

const previewer = Vue.extend({
  render(h) {
    return h('div', this.$slots.footer)
  },
})

test('button for adding to closet should be disabled if not auth', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  expect(wrapper.find('.btn').attributes('disabled')).toBe('disabled')
})

test('button for adding to closet should be enabled if auth', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  Object.assign(window.blessing.extra, { inCloset: true, currentUid: 1 })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  expect(wrapper.find('[data-test="removeFromCloset"]').exists()).toBeTrue()
})

test('likes count indicator', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: { likes: 2 } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  await flushPromises()
  expect(wrapper.find('.likes').classes()).toContain('text-red')
  expect(wrapper.find('.likes').text()).toContain('2')
})

test('render basic information', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      name: 'my-texture',
      type: 'alex',
      hash: '123',
      size: 2,
      upload_at: '2018',
    },
  })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  const text = wrapper.find('.card-primary').text()
  expect(text).toContain('my-texture')
  expect(text).toContain('alex')
  expect(text).toContain('123...')
  expect(text).toContain('2 KB')
  expect(text).toContain('2018')
  expect(text).toContain('author')
})

test('render action text of editing texture name', async () => {
  Object.assign(window.blessing.extra, { admin: true })
  Vue.prototype.$http.get.mockResolvedValue({ data: { uploader: 1, name: 'name' } })

  let wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  expect(wrapper.contains('small')).toBeTrue()

  Object.assign(window.blessing.extra, { currentUid: 2, admin: false })
  wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  expect(wrapper.contains('small')).toBeFalse()
})

test('render nickname of uploader', () => {
  Object.assign(window.blessing.extra, { nickname: null })
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  expect(wrapper.text()).toContain('general.unexistent-user')
})

test('operation panel should not be rendered if user is anonymous', async () => {
  Object.assign(window.blessing.extra, { currentUid: 0 })
  Vue.prototype.$http.get.mockResolvedValue({ data: { uploader: 1 } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  expect(wrapper.find('.card-warning').exists()).toBeFalse()
})

test('operation panel should not be rendered if not privileged', async () => {
  Object.assign(window.blessing.extra, { currentUid: 2 })
  Vue.prototype.$http.get.mockResolvedValue({ data: { uploader: 1 } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  expect(wrapper.find('.card-warning').exists()).toBeFalse()
})

test('operation panel should be rendered if privileged', async () => {
  Object.assign(window.blessing.extra, { currentUid: 1 })
  Vue.prototype.$http.get.mockResolvedValue({ data: { uploader: 1 } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  expect(wrapper.find('.card-warning').exists()).toBeTrue()
})

test('download texture', async () => {
  Object.assign(window.blessing.extra, { currentUid: 1 })
  Vue.prototype.$http.get.mockResolvedValue({ data: { tid: 1, name: 'abc' } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  wrapper.find('[data-test="download"]').trigger('click')
})

test('link to downloading texture', async () => {
  Object.assign(window.blessing.extra, { download: false })
  Vue.prototype.$http.get.mockResolvedValue({ data: { name: '', hash: '123' } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  expect(wrapper.find('span[title="123"]').exists()).toBeTrue()
})

test('set as avatar', async () => {
  Object.assign(window.blessing.extra, { currentUid: 1, inCloset: true })
  Vue.prototype.$http.get.mockResolvedValueOnce({ data: { type: 'steve' } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  await flushPromises()
  wrapper.find('[data-test="setAsAvatar"]').trigger('click')
  expect(Vue.prototype.$confirm).toBeCalled()
})

test('hide "set avatar" button when texture is cape', async () => {
  Vue.prototype.$http.get.mockResolvedValueOnce({ data: { type: 'cape' } })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  await flushPromises()
  expect(wrapper.find('[data-test="setAsAvatar"]').exists()).toBeFalse()
})

test('add to closet', async () => {
  Object.assign(window.blessing.extra, { currentUid: 1, inCloset: false })
  Vue.prototype.$http.get.mockResolvedValue({ data: { name: 'wow', likes: 2 } })
  Vue.prototype.$http.post.mockResolvedValue({ code: 0, message: '' })
  Vue.prototype.$prompt.mockResolvedValue({ value: 'a' } as MessageBoxData)
  const wrapper = mount<Component>(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  wrapper.find('[data-test="addToCloset"]').trigger('click')
  await flushPromises()
  expect(wrapper.vm.likes).toBe(3)
  expect(wrapper.vm.liked).toBeTrue()
})

test('remove from closet', async () => {
  Object.assign(window.blessing.extra, { currentUid: 1, inCloset: true })
  Vue.prototype.$http.get.mockResolvedValue({ data: { likes: 2 } })
  Vue.prototype.$http.post.mockResolvedValue({ code: 0 })
  const wrapper = mount<Component>(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  wrapper.find('[data-test="removeFromCloset"]').trigger('click')
  await flushPromises()
  expect(wrapper.vm.likes).toBe(1)
  expect(wrapper.vm.liked).toBeFalse()
})

test('change texture name', async () => {
  Object.assign(window.blessing.extra, { admin: true })
  Vue.prototype.$http.get.mockResolvedValue({ data: { name: 'old-name' } })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  Vue.prototype.$prompt
    .mockImplementationOnce(() => Promise.reject('cancel'))
    .mockImplementation((_, { inputValidator }) => {
      if (inputValidator) {
        inputValidator('')
        inputValidator('new-name')
      }
      return Promise.resolve({ value: 'new-name' } as MessageBoxData)
    })
  const wrapper = mount<Component>(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  const button = wrapper.find('small > a')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/skinlib/rename',
    { tid: 1, new_name: 'new-name' }
  )
  expect(Vue.prototype.$message.error).toBeCalledWith('1')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.vm.name).toBe('new-name')
})

test('change texture model', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: { type: 'steve' } })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  Vue.prototype.$msgbox
    .mockImplementationOnce(() => Promise.reject())
    .mockImplementation(options => {
      if (options.message) {
        const vnode = options.message as Vue.VNode
        const elm = document.createElement('select')
        elm.appendChild(document.createElement('option'))
        elm.appendChild(document.createElement('option'))
        elm.selectedIndex = 1
        ;(vnode.children as Vue.VNode[])[1].elm = elm
      }
      return Promise.resolve({} as MessageBoxData)
    })
  const wrapper = mount<Component>(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  const button = wrapper
    .findAll('small')
    .at(1)
    .find('a')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/skinlib/model',
    { tid: 1, model: 'alex' }
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('1')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.vm.type).toBe('alex')
})

test('toggle privacy', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: { public: true } })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce('')
    .mockResolvedValue('confirm')
  const wrapper = mount<Component>(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  const button = wrapper.find('.btn-warning')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/skinlib/privacy',
    { tid: 1 }
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('1')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.vm.public).toBeFalse()

  button.trigger('click')
  await flushPromises()
  expect(wrapper.vm.public).toBeTrue()
})

test('delete texture', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValue({ code: 0, message: '0' })
  Vue.prototype.$confirm
    .mockRejectedValueOnce('')
    .mockResolvedValue('confirm')
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  const button = wrapper.find('.btn-danger')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/skinlib/delete',
    { tid: 1 }
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('1')

  button.trigger('click')
  await flushPromises()
  jest.runAllTimers()
  expect(Vue.prototype.$message.success).toBeCalledWith('0')
})

test('report texture', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: { report: 0 } })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'duplicated' })
    .mockResolvedValue({ code: 0, message: 'success' })
  Vue.prototype.$prompt
    .mockRejectedValueOnce('')
    .mockRejectedValueOnce('')
    .mockResolvedValue({ value: 'reason' } as MessageBoxData)
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })

  const button = wrapper.find('[data-test=report]')
  button.trigger('click')
  expect(Vue.prototype.$prompt).toBeCalledWith('', {
    title: 'skinlib.report.title',
    inputPlaceholder: 'skinlib.report.reason',
  })
  expect(Vue.prototype.$http.post).not.toBeCalled()

  wrapper.setData({ reportScore: -5 })
  button.trigger('click')
  expect(Vue.prototype.$prompt).toBeCalledWith('skinlib.report.negative', {
    title: 'skinlib.report.title',
    inputPlaceholder: 'skinlib.report.reason',
  })

  wrapper.setData({ reportScore: 5 })
  button.trigger('click')
  expect(Vue.prototype.$prompt).toBeCalledWith('skinlib.report.positive', {
    title: 'skinlib.report.title',
    inputPlaceholder: 'skinlib.report.reason',
  })
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/skinlib/report',
    { tid: 1, reason: 'reason' }
  )
  expect(Vue.prototype.$message.warning).toBeCalledWith('duplicated')

  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$message.success).toBeCalledWith('success')
})

test('apply texture to player', () => {
  Vue.prototype.$http.get
    .mockResolvedValue({ data: {} })
    .mockResolvedValue([])
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
    stubs: { previewer },
  })
  wrapper.find('[data-target="#modal-use-as"]').trigger('click')
  expect(wrapper.find('[data-target="#modal-add-player"]').exists()).toBeFalse()
})

test('truncate too long texture name', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      name: 'very-very-long-texture-name',
    },
  })
  const wrapper = mount(Show, {
    mocks: {
      $route: ['/skinlib/show/1', '1'],
    },
  })
  await flushPromises()
  expect(wrapper.find('.card-primary').text()).toContain('very-very-long-...')
})
