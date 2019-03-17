import { trans } from '@/js/i18n'
import Vue from 'vue'

test('mount to global', () => {
  expect(window.trans).toBe(trans)
})

test('translate text', () => {
  window.blessing.i18n = { a: { b: { c: 'text', d: 'Hi, :name!' } } }
  expect(trans('a.b.c')).toBe('text')
  expect(trans('a.b.d')).toBe('Hi, :name!')
  expect(trans('a.b.d', { name: 'me' })).toBe('Hi, me!')
  expect(trans('d.e')).toBe('d.e')
})

test('Vue directive', () => {
  const byString = Vue.extend({
    render(h) {
      return h('div', {
        directives: [
          {
            name: 't',
            value: 'abc',
          },
        ],
      })
    },
  })
  expect((new Vue(byString)).$mount().$el.textContent).toBe('abc')

  const byObject = Vue.extend({
    render(h) {
      return h('div', {
        directives: [
          {
            name: 't',
            value: { path: 'abc', args: '123' },
          },
        ],
      })
    },
  })
  expect((new Vue(byObject)).$mount().$el.textContent).toBe('abc')

  const invalid = Vue.extend({
    render(h) {
      return h('div', {
        directives: [
          {
            name: 't',
            value: 123,
          },
        ],
      })
    },
  })
  expect((new Vue(invalid)).$mount().$el.textContent).toBe('')
})
