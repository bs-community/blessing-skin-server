import * as net from '@/js/net'
import { on } from '@/js/event'
import { showAjaxError } from '@/js/notify'

jest.mock('@/js/notify')

window.Request = function Request(url, init) {
  this.url = url
  Object.keys(init).forEach(key => (this[key] = init[key]))
  this.headers = new Map(Object.entries(init.headers))
}

test('the GET method', async () => {
  const json = jest.fn().mockResolvedValue({})
  window.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json,
    headers: new Map([['Content-Type', 'application/json']]),
  })

  const stub = jest.fn()
  on('beforeFetch', stub)

  await net.get('/abc', { a: 'b' })
  expect(stub).toBeCalledWith({
    method: 'GET',
    url: '/abc',
    data: { a: 'b' },
  })
  expect(window.fetch.mock.calls[0][0].url).toBe('/abc?a=b')
  expect(json).toBeCalled()

  await net.get('/abc')
  expect(window.fetch.mock.calls[1][0].url).toBe('/abc')
})

test('the POST method', async () => {
  window.fetch = jest.fn()
    .mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({}),
      headers: new Map([['Content-Type', 'application/json']]),
    })

  const meta = document.createElement('meta')
  meta.name = 'csrf-token'
  meta.content = 'token'
  document.head.appendChild(meta)

  const stub = jest.fn()
  on('beforeFetch', stub)

  const formData = new FormData()
  await net.post('/abc', formData)
  expect(stub).toBeCalledWith({
    method: 'POST',
    url: '/abc',
    data: formData,
  })

  await net.post('/abc', { a: 'b' })
  expect(stub).toBeCalledWith({
    method: 'POST',
    url: '/abc',
    data: { a: 'b' },
  })
  // eslint-disable-next-line prefer-destructuring
  const request = window.fetch.mock.calls[1][0]
  expect(request.url).toBe('/abc')
  expect(request.method).toBe('POST')
  expect(request.body).toBe(JSON.stringify({ a: 'b' }))
  expect(request.headers.get('X-CSRF-TOKEN')).toBe('token')
  expect(request.headers.get('Content-Type')).toBe('application/json')

  await net.post('/abc')
  expect(window.fetch.mock.calls[2][0].body).toBe('{}')
})

test('low level fetch', async () => {
  const json = jest.fn().mockResolvedValue({})
  window.fetch = jest.fn()
    .mockRejectedValueOnce(new Error('network'))
    .mockResolvedValueOnce({
      ok: false,
      text: () => Promise.resolve('404'),
      clone: () => ({}),
    })
    .mockResolvedValueOnce({
      ok: true,
      json,
      headers: new Map([['Content-Type', 'application/json']]),
    })
    .mockResolvedValueOnce({
      ok: true,
      headers: new Map(),
      text: () => Promise.resolve('text'),
    })

  const request = { headers: new Map() }

  const stub = jest.fn()
  on('fetchError', stub)

  await net.walkFetch(request)
  expect(showAjaxError.mock.calls[0][0]).toBeInstanceOf(Error)
  expect(showAjaxError.mock.calls[0][0]).toHaveProperty('message', 'network')
  expect(stub).toBeCalledWith(expect.any(Error))

  await net.walkFetch(request)
  expect(showAjaxError.mock.calls[1][0]).toBeInstanceOf(Error)
  expect(stub.mock.calls[1][0]).toHaveProperty('message', '404')
  expect(stub.mock.calls[1][0]).toHaveProperty('response')

  await net.walkFetch(request)
  expect(json).toBeCalled()

  expect(await net.walkFetch(request)).toBe('text')
})
