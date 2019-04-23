/* eslint-disable no-mixed-operators */
import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { Button } from 'element-ui'
import Dashboard from '@/views/user/Dashboard.vue'

jest.mock('@tweenjs/tween.js', () => ({
  Tween: class <T> {
    data: T

    constructor(data: T) {
      this.data = data
    }

    to(data: Partial<T>, _: number) {
      Object.assign(this.data, data)
      return this
    }

    start() {}
  },
  update() {},
}))

window.blessing.extra = { unverified: false }

function scoreInfo(data = {}) {
  return {
    user: { score: 835, lastSignAt: '2018-08-07 16:06:49' },
    stats: {
      players: {
        used: 3, total: 15, percentage: 20,
      },
      storage: {
        used: 5, total: 20, percentage: 25,
      },
    },
    signAfterZero: false,
    signGapTime: '24',
    ...data,
  }
}

test('fetch score info', () => {
  Vue.prototype.$http.get.mockResolvedValue(scoreInfo())
  mount(Dashboard)
  expect(Vue.prototype.$http.get).toBeCalledWith('/user/score-info')
})

test('players usage', async () => {
  Vue.prototype.$http.get.mockResolvedValue(scoreInfo())
  const wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.text()).toContain('3 / 15')
})

test('storage usage', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce(scoreInfo())
    .mockResolvedValueOnce(scoreInfo({
      stats: {
        players: {
          used: 3, total: 15, percentage: 20,
        },
        storage: {
          used: 2048, total: 4096, percentage: 50,
        },
      },
    }))
  let wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.text()).toContain('5 / 20 KB')

  wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.text()).toContain('2 / 4 MB')
})

test('display score', async () => {
  Vue.prototype.$http.get.mockResolvedValue(scoreInfo())
  const wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.find('#score').text()).toContain('835')
})

test('button `sign` state', async () => {
  Vue.prototype.$http.get
    .mockResolvedValueOnce(scoreInfo({ signAfterZero: true }))
    .mockResolvedValueOnce(scoreInfo({
      signAfterZero: true,
      user: { lastSignAt: Date.now() },
    }))
    .mockResolvedValueOnce(scoreInfo({ user: { lastSignAt: Date.now() - 25 * 3600 * 1000 } }))
    .mockResolvedValueOnce(scoreInfo({ user: { lastSignAt: Date.now() } }))

  let wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.find(Button).attributes('disabled')).toBeNil()

  wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.find(Button).attributes('disabled')).toBe('disabled')

  wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.find(Button).attributes('disabled')).toBeNil()

  wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.find(Button).attributes('disabled')).toBe('disabled')
})

test('remaining time', async () => {
  const origin = Vue.prototype.$t
  Vue.prototype.$t = (key, args) => key + JSON.stringify(args)

  Vue.prototype.$http.get
    .mockResolvedValueOnce(scoreInfo({
      user: { lastSignAt: Date.now() - 23.5 * 3600 * 1000 },
    }))
    .mockResolvedValueOnce(scoreInfo({
      user: { lastSignAt: Date.now() },
    }))

  let wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.find(Button).text()).toMatch(/(29)|(30)/)
  expect(wrapper.find(Button).text()).toContain('min')

  wrapper = mount(Dashboard)
  await wrapper.vm.$nextTick()
  expect(wrapper.find(Button).text()).toContain('23')
  expect(wrapper.find(Button).text()).toContain('hour')

  Vue.prototype.$t = origin
})

test('sign', async () => {
  Vue.prototype.$http.get.mockResolvedValue(scoreInfo({
    user: { lastSignAt: Date.now() - 30 * 3600 * 1000 },
  }))
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({
      code: 0,
      score: 233,
      storage: { used: 3, total: 4 },
    })
  const wrapper = mount(Dashboard)
  const button = wrapper.find(Button)
  await wrapper.vm.$nextTick()

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(Vue.prototype.$http.post).toBeCalledWith('/user/sign')
  expect(Vue.prototype.$message.warning).toBeCalledWith('1')

  button.trigger('click')
  await wrapper.vm.$nextTick()
  expect(button.attributes('disabled')).toBe('disabled')
  expect(wrapper.text()).toContain('3 / 4 KB')
})
