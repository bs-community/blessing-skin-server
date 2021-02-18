import { t } from '@/scripts/i18n'

test('mount to global', () => {
  expect(window.trans).toBe(t)
})

test('translate text', () => {
  window.blessing.i18n = { a: { b: { c: 'text', d: 'Hi, :name!' } } }
  expect(t('a.b.c')).toBe('text')
  expect(t('a.b.d')).toBe('Hi, :name!')
  expect(t('a.b.d', { name: 'me' })).toBe('Hi, me!')
  expect(t('a.b.e')).toBe('a.b.e')
  expect(t('d.e')).toBe('d.e')
})
