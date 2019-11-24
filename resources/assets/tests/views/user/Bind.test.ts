import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { flushPromises } from '../../utils'
import Bind from '@/views/user/Bind.vue'

test('list existed players', async () => {
  Vue.prototype.$http.get
    .mockResolvedValue({ data: [{ name: 'a' }, { name: 'b' }] })
  const wrapper = mount(Bind)
  await flushPromises()
  const options = wrapper.findAll('option')
  expect(options).toHaveLength(2)
})

test('show input box', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [] })
  const wrapper = mount(Bind)
  await flushPromises()
  const input = wrapper.find('input')
  expect(input.exists()).toBeTrue()
})

test('submit', async () => {
  Vue.prototype.$http.get.mockResolvedValue({ data: [] })
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: 'fail' })
    .mockResolvedValueOnce({ code: 0, message: 'ok' })

  const wrapper = mount(Bind)
  const form = wrapper.find('form')
  wrapper.find('input').setValue('abc')

  form.trigger('submit')
  await flushPromises()
  expect(wrapper.find('.alert').text()).toBe('fail')

  form.trigger('submit')
  await flushPromises()
  expect(Vue.prototype.$alert).toBeCalledWith('ok')
})
