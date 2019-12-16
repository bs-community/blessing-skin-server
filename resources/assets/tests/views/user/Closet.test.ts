import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import Closet from '@/views/user/Closet.vue'
import ClosetItem from '@/components/ClosetItem.vue'
import Previewer from '@/components/Previewer.vue'

beforeEach(() => {
  window.blessing.extra = { unverified: false }
})

test('fetch closet data before mount', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  mount(Closet)
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/user/closet-data',
    {
      category: 'skin',
      q: '',
      page: 1,
    },
  )
})

test('switch tabs', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [],
      category: 'skin',
      total_pages: 1,
    },
  }).mockResolvedValueOnce({
    data: {
      items: [],
      category: 'cape',
      total_pages: 1,
    },
  })

  const wrapper = mount(Closet)

  wrapper
    .findAll('.nav-link')
    .at(0)
    .trigger('click')
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/user/closet-data',
    {
      category: 'skin',
      q: '',
      page: 1,
    },
  )
  wrapper
    .findAll('.nav-link')
    .at(1)
    .trigger('click')
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/user/closet-data',
    {
      category: 'cape',
      q: '',
      page: 1,
    },
  )
})

test('different categories', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })

  const wrapper = mount(Closet)
  expect(
    wrapper
      .findAll('.nav-link')
      .at(0)
      .classes('active'),
  ).toBeTrue()
  expect(wrapper.find('#skin-category').classes('active')).toBeTrue()

  wrapper.setData({ category: 'cape' })
  expect(
    wrapper
      .findAll('.nav-link')
      .at(1)
      .classes('active'),
  ).toBeTrue()
  expect(wrapper.find('#cape-category').classes('active')).toBeTrue()
})

test('search textures', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })

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
    },
  )
})

test('empty closet', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount(Closet)
  expect(wrapper.find('#skin-category').text()).toContain('user.emptyClosetMsg')
  wrapper.setData({ category: 'cape' })
  expect(wrapper.find('#cape-category').text()).toContain('user.emptyClosetMsg')
})

test('no matched search result', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount(Closet)
  wrapper.setData({ query: 'q' })
  expect(wrapper.find('#skin-category').text()).toContain('general.noResult')
  wrapper.setData({ category: 'cape' })
  expect(wrapper.find('#cape-category').text()).toContain('general.noResult')
})

test('render items', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [
        { tid: 1 },
        { tid: 2 },
      ],
      category: 'skin',
      total_pages: 1,
    },
  })
  const wrapper = mount(Closet)
  await flushPromises()
  expect(wrapper.findAll(ClosetItem)).toHaveLength(2)
})

test('reload closet when page changed', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount<Vue & { pageChanged(): void }>(Closet)
  wrapper.vm.pageChanged()
  jest.runAllTicks()
  expect(Vue.prototype.$http.get).toBeCalledTimes(2)
})

test('remove skin item', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount<Vue & { removeSkinItem(tid: number): void }>(Closet)
  wrapper.setData({ skinItems: [{ tid: 1 }] })
  wrapper.vm.removeSkinItem(0)
  expect(wrapper.find('#skin-category').text()).toContain('user.emptyClosetMsg')
})

test('remove cape item', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount<Vue & { removeCapeItem(tid: number): void }>(Closet)
  wrapper.setData({ capeItems: [{ tid: 1 }], category: 'cape' })
  wrapper.vm.removeCapeItem(0)
  expect(wrapper.find('#cape-category').text()).toContain('user.emptyClosetMsg')
})

test('select texture', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce({ data: {} })
    .mockResolvedValueOnce({ data: { type: 'steve', hash: 'a' } })
    .mockResolvedValueOnce({ data: { type: 'cape', hash: 'b' } })

  const wrapper = mount<Vue & { skinUrl: string, capeUrl: string }>(Closet)
  wrapper.setData({ skinItems: [{ tid: 1 }] })
  wrapper.find(ClosetItem).vm.$emit('select')
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/1')
  expect(wrapper.vm.skinUrl).toBe('/textures/a')

  wrapper.setData({
    skinItems: [], capeItems: [{ tid: 2 }], category: 'cape',
  })
  wrapper.find(ClosetItem).vm.$emit('select')
  await flushPromises()
  expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/2')
  expect(wrapper.vm.capeUrl).toBe('/textures/b')
})

test('apply texture', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce({ data: {} })
    .mockResolvedValueOnce({ data: [] })
    .mockResolvedValueOnce({
      data: [
        {
          pid: 1, name: 'name', tid_skin: 10,
        },
      ],
    })

  const wrapper = mount(Closet)
  const button = wrapper.find(Previewer).findAll('button')
    .at(0)
  button.trigger('click')
  jest.runAllTicks()
  expect(wrapper.find('.modal-body').text()).toContain('user.closet.use-as.empty')

  button.trigger('click')
  await flushPromises()
  expect(wrapper.find('input[type="radio"]').attributes('value')).toBe('1')
  expect(wrapper.find('.model-label > img').attributes('src')).toBe('/avatar/35/10')
  expect(wrapper.find('.modal-body').text()).toContain('name')
  jest.runAllTimers()
})

test('reset selected texture', () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: {} })
  const wrapper = mount(Closet)
  wrapper.setData({
    selectedSkin: 1,
    selectedCape: 2,
    skinUrl: 'a',
    capeUrl: 'b',
  })
  wrapper.find('[data-test="resetSelected"]').trigger('click')
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
  }))
  Vue.prototype.$http.get
    .mockResolvedValueOnce({
      data: {
        items: [],
        category: 'skin',
        total_pages: 1,
      },
    })
    .mockResolvedValueOnce({ data: { type: 'cape', hash: '' } })
    .mockResolvedValueOnce([])
  mount(Closet)
  jest.runAllTimers()
  await flushPromises()
  jest.unmock('@/scripts/utils')
})
