import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Report from '@/views/user/Report.vue'

test('basic render', async () => {
  Vue.prototype.$http.get.mockResolvedValue([
    {
      id: 1, tid: 1, reason: 'abc', status: 1,
    },
  ])
  const wrapper = mount(Report)
  await wrapper.vm.$nextTick()

  expect(wrapper.find('a').attributes('href')).toBe('/skinlib/show/1')
  expect(wrapper.text()).toContain('report.status.1')
})
