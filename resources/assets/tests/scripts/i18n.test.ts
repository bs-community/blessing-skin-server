import { trans } from '@/scripts/i18n'

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
