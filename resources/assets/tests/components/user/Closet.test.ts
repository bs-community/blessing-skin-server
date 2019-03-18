import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Closet from '@/components/user/Closet.vue'
import ClosetItem from '@/components/user/ClosetItem.vue'
import Previewer from '@/components/common/Previewer.vue'
import toastr from 'toastr'
import { swal } from '@/js/notify'

jest.mock('@/js/notify')

window.blessing.extra = { unverified: false }

test('fetch closet data before mount', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  mount(Closet)
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/user/closet-data',
    {
      category: 'skin',
      q: '',
      page: 1,
    }
  )
})

test('switch tabs', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    items: [],
    category: 'skin',
    total_pages: 1,
  }).mockResolvedValueOnce({
    items: [],
    category: 'cape',
    total_pages: 1,
  })

  const wrapper = mount(Closet)

  const tabSkin = wrapper.findAll('.nav-tabs > li').at(0)
  tabSkin.find('a').trigger('click')
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/user/closet-data',
    {
      category: 'skin',
      q: '',
      page: 1,
    }
  )
  const tabCape = wrapper.findAll('.nav-tabs > li').at(1)
  tabCape.find('a').trigger('click')
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/user/closet-data',
    {
      category: 'cape',
      q: '',
      page: 1,
    }
  )
})

test('different categories', () => {
  Vue.prototype.$http.get.mockResolvedValue({})

  const wrapper = mount(Closet)
  expect(wrapper.findAll('.nav-tabs > li').at(0)
    .classes('active')).toBeTrue()
  expect(wrapper.find('#skin-category').classes('active')).toBeTrue()

  wrapper.setData({ category: 'cape' })
  expect(wrapper.findAll('.nav-tabs > li').at(1)
    .classes('active')).toBeTrue()
  expect(wrapper.find('#cape-category').classes('active')).toBeTrue()
})

test('search textures', () => {
  Vue.prototype.$http.get.mockResolvedValue({})

  const wrapper = mount(Closet)
  const input = wrapper.find('input')
  ;(input.element as HTMLInputElement).value = 'q'
  input.trigger('input')
  jest.runAllTimers()
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/user/closet-data',
    {
      category: 'skin',
      q: 'q',
      page: 1,
    }
  )
})

test('empty closet', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  const wrapper = mount(Closet)
  expect(wrapper.find('#skin-category').text()).toContain('user.emptyClosetMsg')
  wrapper.setData({ category: 'cape' })
  expect(wrapper.find('#cape-category').text()).toContain('user.emptyClosetMsg')
})

test('no matched search result', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  const wrapper = mount(Closet)
  wrapper.setData({ query: 'q' })
  expect(wrapper.find('#skin-category').text()).toContain('general.noResult')
  wrapper.setData({ category: 'cape' })
  expect(wrapper.find('#cape-category').text()).toContain('general.noResult')
})

test('render items', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    items: [
      { tid: 1 },
      { tid: 2 },
    ],
    category: 'skin',
    total_pages: 1,
  })
  const wrapper = mount(Closet)
  await wrapper.vm.$nextTick()
  expect(wrapper.findAll(ClosetItem)).toHaveLength(2)
})

test('reload closet when page changed', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  const wrapper = mount<Vue & { pageChanged(): void }>(Closet)
  wrapper.vm.pageChanged()
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledTimes(2)
})

test('remove skin item', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  const wrapper = mount<Vue & { removeSkinItem(tid: number): void }>(Closet)
  wrapper.setData({ skinItems: [{ tid: 1 }] })
  wrapper.vm.removeSkinItem(0)
  expect(wrapper.find('#skin-category').text()).toContain('user.emptyClosetMsg')
})

test('remove cape item', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  const wrapper = mount<Vue & { removeCapeItem(tid: number): void }>(Closet)
  wrapper.setData({ capeItems: [{ tid: 1 }], category: 'cape' })
  wrapper.vm.removeCapeItem(0)
  expect(wrapper.find('#cape-category').text()).toContain('user.emptyClosetMsg')
})

test('compute avatar URL', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  // eslint-disable-next-line camelcase
  const wrapper = mount<Vue & { avatarUrl(player: { tid_skin: number }): string }>(Closet)
  const { avatarUrl } = wrapper.vm
  expect(avatarUrl({ tid_skin: 1 })).toBe('/avatar/35/1')
})

test('select texture', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce({})
    .mockResolvedValueOnce({ type: 'steve', hash: 'a' })
    .mockResolvedValueOnce({ type: 'cape', hash: 'b' })

  const wrapper = mount<Vue & { skinUrl: string, capeUrl: string }>(Closet)
  wrapper.setData({ skinItems: [{ tid: 1 }] })
  wrapper.find(ClosetItem).vm.$emit('select')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/1')
  expect(wrapper.vm.skinUrl).toBe('/textures/a')

  wrapper.setData({
    skinItems: [], capeItems: [{ tid: 2 }], category: 'cape',
  })
  wrapper.find(ClosetItem).vm.$emit('select')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/2')
  expect(wrapper.vm.capeUrl).toBe('/textures/b')
})

test('apply texture', async () => {
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
  Vue.prototype.$http.get
    .mockResolvedValueOnce({})
    .mockResolvedValueOnce([])
    .mockResolvedValueOnce([
      {
        pid: 1, name: 'name', tid_skin: 10,
      },
    ])

  const wrapper = mount(Closet)
  const button = wrapper.find(Previewer).findAll('button')
    .at(0)
  button.trigger('click')
  jest.runAllTicks()
  expect(wrapper.find('.modal-body').text()).toContain('user.closet.use-as.empty')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(wrapper.find('input[type="radio"]').attributes('value')).toBe('1')
  expect(wrapper.find('.model-label > img').attributes('src')).toBe('/avatar/35/10')
  expect(wrapper.find('.modal-body').text()).toContain('name')
  jest.runAllTimers()
})

test('submit applying texture', async () => {
  window.$ = jest.fn(() => ({ modal() {} }))
  jest.spyOn(toastr, 'info')
  Vue.prototype.$http.get.mockResolvedValue({})
  Vue.prototype.$http.post.mockResolvedValueOnce({ errno: 1 })
    .mockResolvedValue({ errno: 0, msg: 'ok' })
  const wrapper = mount(Closet)
  const button = wrapper.find('.modal-footer > a:nth-child(2)')

  button.trigger('click')
  expect(toastr.info).toBeCalledWith('user.emptySelectedPlayer')

  wrapper.setData({ selectedPlayer: 1 })
  button.trigger('click')
  expect(toastr.info).toBeCalledWith('user.emptySelectedTexture')

  wrapper.setData({ selectedSkin: 1 })
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/set',
    {
      pid: 1,
      tid: {
        skin: 1,
        cape: undefined,
      },
    }
  )
  wrapper.setData({ selectedSkin: 0, selectedCape: 1 })
  button.trigger('click')
  expect(Vue.prototype.$http.post).toBeCalledWith(
    '/user/player/set',
    {
      pid: 1,
      tid: {
        skin: undefined,
        cape: 1,
      },
    }
  )
  await wrapper.vm.$nextTick()
  expect(swal).toBeCalledWith({ type: 'success', text: 'ok' })
})

test('reset selected texture', () => {
  Vue.prototype.$http.get.mockResolvedValue({})
  const wrapper = mount(Closet)
  wrapper.setData({
    selectedSkin: 1,
    selectedCape: 2,
    skinUrl: 'a',
    capeUrl: 'b',
  })
  wrapper.find(Previewer).findAll('button')
    .at(1)
    .trigger('click')
  expect(wrapper.vm).toEqual(expect.objectContaining({
    selectedSkin: 0,
    selectedCape: 0,
    skinUrl: '',
    capeUrl: '',
  }))
})

test('select specified texture initially', async () => {
  window.history.pushState({}, 'title', `${location.href}?tid=1`)
  window.$ = jest.fn(() => ({
    modal() {},
    iCheck: () => ({
      on(_: Event, cb: CallableFunction) {
        cb()
      },
    }),
    0: {
      dispatchEvent: () => {},
    },
  }))
  Vue.prototype.$http.get
    .mockResolvedValueOnce({
      items: [],
      category: 'skin',
      total_pages: 1,
    })
    .mockResolvedValueOnce({ type: 'cape', hash: '' })
    .mockResolvedValueOnce([])
  const wrapper = mount(Closet)
  jest.runAllTimers()
  await wrapper.vm.$nextTick()
  jest.unmock('@/js/utils')
})
