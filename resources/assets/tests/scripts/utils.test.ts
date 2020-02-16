import * as utils from '@/scripts/utils'

test('queryString', () => {
  history.pushState({}, 'page', `${location.href}?key=value`)
  expect(utils.queryString('key')).toBe('value')
  expect(utils.queryString('a')).toBe('')
  expect(utils.queryString('a', 'b')).toBe('b')
})

test('queryStringify', () => {
  expect(utils.queryStringify({ a: 'b', c: 'd' })).toBe('a=b&c=d')
})
