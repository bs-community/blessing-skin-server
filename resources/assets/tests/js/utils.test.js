import * as utils from '@/js/utils'

test('debounce', () => {
  const stub = jest.fn()
  const debounced = utils.debounce(stub, 2000)

  debounced()
  debounced()
  expect(stub).not.toBeCalled()
  jest.runAllTimers()
  expect(stub).toBeCalledTimes(1)
})

test('queryString', () => {
  history.pushState({}, 'page', `${location.href}?key=value`)
  expect(utils.queryString('key')).toBe('value')
  expect(utils.queryString('a')).toBeUndefined()
  expect(utils.queryString('a', 'b')).toBe('b')
})

test('queryStringify', () => {
  expect(utils.queryStringify({ a: 'b', c: 'd' })).toBe('a=b&c=d')
})
