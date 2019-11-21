import Vue from 'vue'
import { mount } from '@vue/test-utils'
// @ts-ignore
import Button from 'element-ui/lib/button'
import { flushPromises } from '../../utils'
import { queryString } from '@/scripts/utils'
import List from '@/views/skinlib/List.vue'

jest.mock('element-ui', () => ({
  Select: {
    install(vue: typeof Vue) {
      vue.component('ElSelect', {
        render(h) {
          return h('select', {
            on: {
              change: (event: Event) => this.$emit(
                'change',
                (event.target as HTMLSelectElement).value
              ),
            },
            attrs: {
              value: this.value,
            },
          }, this.$slots.default)
        },
        props: {
          value: String,
        },
        model: {
          prop: 'value',
          event: 'change',
        },
      })
    },
  },
  Option: {
    install(vue: typeof Vue) {
      vue.component('ElOption', {
        render(h) {
          return h('option', { attrs: { value: this.value } }, this.label)
        },
        props: {
          label: String,
          value: String,
        },
      })
    },
  },
  ButtonGroup: {
    install(vue: typeof Vue) {
      vue.component('ElButtonGroup', {
        render(h) {
          return h('div', {}, this.$slots.default)
        },
      })
    },
  },
}))

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
    }
  )
})

test('empty skin library', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)
  expect(wrapper.text()).toContain('general.noResult')
})

test('toggle texture type', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)
  const select = wrapper.find({ name: 'ElSelect' })
  const breadcrumb = wrapper.find('.breadcrumb')
  expect(breadcrumb.text()).toContain('skinlib.filter.skin')

  select.setValue('steve')
  select.trigger('change')
  expect(breadcrumb.text()).toContain('skinlib.filter.steve')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'steve', uploader: 0, sort: 'time', keyword: '', page: 1,
    }
  )
  expect(queryString('filter')).toBe('steve')
  select.setValue('alex')
  select.trigger('change')
  expect(breadcrumb.text()).toContain('skinlib.filter.alex')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'alex', uploader: 0, sort: 'time', keyword: '', page: 1,
    }
  )
  expect(queryString('filter')).toBe('alex')
  select.setValue('cape')
  select.trigger('change')
  expect(breadcrumb.text()).toContain('general.cape')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'cape', uploader: 0, sort: 'time', keyword: '', page: 1,
    }
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
  const button = wrapper
    .find('.advanced-filter')
    .findAll(Button)
    .at(2)
  expect(breadcrumb.text()).toContain('skinlib.filter.allUsers')

  button.trigger('click')
  expect(breadcrumb.text()).toContain('skinlib.filter.uploader')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 1, sort: 'time', keyword: '', page: 1,
    }
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
  const buttons = wrapper.find('.advanced-filter').findAll(Button)
  const sortByLikes = buttons.at(0)
  const sortByTime = buttons.at(1)

  sortByLikes.trigger('click')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'likes', keyword: '', page: 1,
    }
  )
  expect(wrapper.text()).toContain('skinlib.sort.likes')
  expect(queryString('sort')).toBe('likes')

  sortByTime.trigger('click')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: '', page: 1,
    }
  )
  expect(wrapper.text()).toContain('skinlib.sort.time')
  expect(queryString('sort')).toBe('time')
})

test('search by keyword', () => {
  Vue.prototype.$http.get.mockResolvedValue({
    data: {
      items: [], total_pages: 0, current_uid: 0,
    },
  })
  const wrapper = mount(List)

  wrapper.setData({ keyword: 'a' })
  wrapper.find('form').trigger('submit')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: 'a', page: 1,
    }
  )
  expect(queryString('keyword')).toBe('a')

  wrapper.setData({ keyword: 'b' })
  wrapper.find('[data-test="btn-search"]').trigger('click')
  expect(Vue.prototype.$http.get).toBeCalledWith(
    '/skinlib/data',
    {
      filter: 'skin', uploader: 0, sort: 'time', keyword: 'b', page: 1,
    }
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
  wrapper.findAll('option').at(3)
    .setSelected()
  wrapper.setData({ keyword: 'abc' })
  const buttons = wrapper.find('.advanced-filter').findAll(Button)
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
    }
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
    onLikeToggled(tid: number, like: boolean): void,
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
