import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import { trans } from '@/scripts/i18n'
import { queryString } from '@/scripts/utils'
import List from '@/views/skinlib/List.vue'

beforeEach(() => {
  window.history.pushState(null, '', 'skinlib')
})

test('fetch data before mounting', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  mount(List)
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: '', page: 1,
    },
  )
})

test('empty skin library', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)
  expect(wrapper.text()).toContain(trans('general.noResult'))
})

test('toggle texture type', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)
  const options = wrapper.findAll('.dropdown-item')
  const btnSkin = options.at(0)
  const btnSteve = options.at(1)
  const btnAlex = options.at(2)
  const btnCape = options.at(3)
  const dropdownToggle = wrapper.find('.dropdown-toggle')
  const breadcrumb = wrapper.find('.breadcrumb')

  expect(btnSkin.classes()).toContain('active')
  expect(btnSteve.classes()).not.toContain('active')
  expect(btnAlex.classes()).not.toContain('active')
  expect(btnCape.classes()).not.toContain('active')
  expect(dropdownToggle.text()).toContain(trans('general.skin'))
  expect(breadcrumb.text()).toContain(trans('skinlib.filter.skin'))

  btnSteve.trigger('click')
  expect(btnSkin.classes()).not.toContain('active')
  expect(btnSteve.classes()).toContain('active')
  expect(btnAlex.classes()).not.toContain('active')
  expect(btnCape.classes()).not.toContain('active')
  expect(dropdownToggle.text()).toContain('Steve')
  expect(breadcrumb.text()).toContain(trans('skinlib.filter.steve'))
  expect(queryString('filter')).toBe('steve')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'steve', uploader: 0, sort: 'time', keyword: '', page: 1,
    },
  )

  btnAlex.trigger('click')
  expect(btnSkin.classes()).not.toContain('active')
  expect(btnSteve.classes()).not.toContain('active')
  expect(btnAlex.classes()).toContain('active')
  expect(btnCape.classes()).not.toContain('active')
  expect(dropdownToggle.text()).toContain('Alex')
  expect(breadcrumb.text()).toContain(trans('skinlib.filter.alex'))
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'alex', uploader: 0, sort: 'time', keyword: '', page: 1,
    },
  )
  expect(queryString('filter')).toBe('alex')

  btnCape.trigger('click')
  expect(btnSkin.classes()).not.toContain('active')
  expect(btnSteve.classes()).not.toContain('active')
  expect(btnAlex.classes()).not.toContain('active')
  expect(btnCape.classes()).toContain('active')
  expect(dropdownToggle.text()).toContain(trans('general.cape'))
  expect(breadcrumb.text()).toContain(trans('general.cape'))
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'cape', uploader: 0, sort: 'time', keyword: '', page: 1,
    },
  )
  expect(queryString('filter')).toBe('cape')
})

test('check specified uploader', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 1,
    },
  })
  const wrapper = mount(List)
  await flushPromises()
  const breadcrumb = wrapper.find('.breadcrumb')
  const button = wrapper.findAll('.bg-olive').at(2)
  expect(breadcrumb.text()).toContain(trans('skinlib.filter.allUsers'))

  button.trigger('click')
  expect(button.classes()).toContain('active')
  expect(breadcrumb.text()).toContain(trans('skinlib.filter.uploader', { uid: 1 }))
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 1, sort: 'time', keyword: '', page: 1,
    },
  )
  expect(queryString('uploader')).toBe('1')
})

test('sort items', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)
  const buttons = wrapper.findAll('.bg-olive')
  const sortByLikes = buttons.at(0)
  const sortByTime = buttons.at(1)

  sortByLikes.trigger('click')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'likes', keyword: '', page: 1,
    },
  )
  expect(wrapper.text()).toContain(trans('skinlib.sort.likes'))
  expect(sortByLikes.classes()).toContain('active')
  expect(queryString('sort')).toBe('likes')

  sortByTime.trigger('click')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: '', page: 1,
    },
  )
  expect(wrapper.text()).toContain(trans('skinlib.sort.time'))
  expect(sortByTime.classes()).toContain('active')
  expect(queryString('sort')).toBe('time')
})

test('search by keyword', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)
  const input = wrapper.find('[data-test="keyword"]')

  input.setValue('a')
  wrapper.find('form').trigger('submit')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: 'a', page: 1,
    },
  )
  expect(queryString('keyword')).toBe('a')

  input.setValue('b')
  wrapper.find('[data-test="btn-search"]').trigger('click')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: 'b', page: 1,
    },
  )
  expect(queryString('keyword')).toBe('b')
})

test('reset all filters', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)
  wrapper
    .findAll('.dropdown-item')
    .at(3)
    .trigger('click')
  wrapper.setData({ keyword: 'abc' })
  const buttons = wrapper.findAll('.bg-olive')
  buttons.at(1).trigger('click')

  Vue.prototype.$http.get.mockClear()
  buttons.at(3).trigger('click')
  expect(Vue.prototype.$http.get).toBeCalledTimes(1)
})

test('is anonymous', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount<Vue & { anonymous: boolean }>(List)
  expect(wrapper.vm.anonymous).toBeTrue()
})

test('on page changed', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount<Vue & { pageChanged(page: number): void }>(List)
  wrapper.vm.pageChanged(2)
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: '', page: 2,
    },
  )
  expect(queryString('page')).toBe('2')
})

test('on like toggled', async () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [{
        tid: 1, liked: false, likes: 0,
      }],
      total_pages: 1,
      current_uid: 0,
    },
  })
  const wrapper = mount<Vue & {
    onLikeToggled(tid: number, like: boolean): void
    items: Array<{ liked: boolean, likes: number }>
  }>(List)
  await flushPromises()
  wrapper.vm.onLikeToggled(0, true)
  expect(wrapper.vm.items[0].liked).toBeTrue()
  expect(wrapper.vm.items[0].likes).toBe(1)

  wrapper.vm.onLikeToggled(0, false)
  expect(wrapper.vm.items[0].liked).toBeFalse()
  expect(wrapper.vm.items[0].likes).toBe(0)
})
