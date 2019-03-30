import { mount } from '@vue/test-utils'
import serverTable from '@/components/mixins/serverTable'

test('change current page', () => {
  const wrapper = mount(serverTable)
  jest.spyOn(wrapper.vm, 'fetchData')
  wrapper.vm.onPageChange({ currentPage: 2 })
  expect(wrapper.vm.fetchData).toBeCalled()
  expect(wrapper.vm.serverParams.page).toBe(2)
})

test('change per page', () => {
  const wrapper = mount(serverTable)
  jest.spyOn(wrapper.vm, 'fetchData')
  wrapper.vm.onPerPageChange({ currentPerPage: 2 })
  expect(wrapper.vm.fetchData).toBeCalled()
  expect(wrapper.vm.serverParams.perPage).toBe(2)
})

test('change sort type', () => {
  const wrapper = mount(serverTable)
  jest.spyOn(wrapper.vm, 'fetchData')
  wrapper.setData({
    columns: [
      { field: '0' },
      { field: '1' },
      { field: '2' },
    ],
  })
  wrapper.vm.onSortChange([{ type: 'desc', field: '2' }])
  expect(wrapper.vm.fetchData).toBeCalled()
  expect(wrapper.vm.serverParams.sortType).toBe('desc')
  expect(wrapper.vm.serverParams.sortField).toBe('2')
})

test('search', () => {
  const wrapper = mount(serverTable)
  jest.spyOn(wrapper.vm, 'fetchData')
  wrapper.vm.onSearch({ searchTerm: 'q' })
  expect(wrapper.vm.fetchData).toBeCalled()
  expect(wrapper.vm.serverParams.search).toBe('q')
})
