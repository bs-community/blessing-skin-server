import Vue from 'vue'
import * as net from '@/scripts/net'
import { on } from '@/scripts/event'
import { showAjaxError, showModal } from '@/scripts/notify'

jest.mock('@/scripts/notify')

test('the GET method', async () => {
  const json = jest.fn().mockResolvedValue({})
  window.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json,
    headers: new Map([['Content-Type', 'application/json']]),
    clone: () => ({}),
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
      clone: () => ({}),
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
      headers: new Map(),
      text: () => Promise.resolve('404'),
      clone: () => ({}),
    })
    .mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({ message: 'error' }),
      headers: new Map([['Content-Type', 'application/json']]),
      clone: () => ({}),
    })
    .mockResolvedValueOnce({
      ok: true,
      json,
      headers: new Map([['Content-Type', 'application/json']]),
      clone: () => ({}),
    })
    .mockResolvedValueOnce({
      ok: true,
      headers: new Map(),
      text: () => Promise.resolve('text'),
      clone: () => ({}),
    })

  const request: RequestInit = { headers: new Headers() }

  const stub = jest.fn()
  on('fetchError', stub)

  await net.walkFetch(request as Request)
  expect(showAjaxError.mock.calls[0][0]).toBeInstanceOf(Error)
  expect(showAjaxError.mock.calls[0][0]).toHaveProperty('message', 'network')
  expect(stub).toBeCalledWith(expect.any(Error))

  await net.walkFetch(request as Request)
  expect(showAjaxError.mock.calls[1][0]).toBeInstanceOf(Error)
  expect(stub.mock.calls[1][0]).toHaveProperty('message', '404')
  expect(stub.mock.calls[1][0]).toHaveProperty('response')

  await net.walkFetch(request as Request)
  expect(showAjaxError.mock.calls[2][0]).toBeInstanceOf(Error)
  expect(stub.mock.calls[2][0]).toHaveProperty('message', 'error')
  expect(stub.mock.calls[2][0]).toHaveProperty('response')

  await net.walkFetch(request as Request)
  expect(json).toBeCalled()

  expect(await net.walkFetch(request as Request)).toBe('text')
})

test('process backend errors', async () => {
  window.fetch = jest.fn()
    .mockResolvedValueOnce({
      status: 422,
      headers: new Map([['Content-Type', 'application/json']]),
      json() {
        return Promise.resolve({
          errors: { name: ['required'] },
        })
      },
      clone: () => ({}),
    })
    .mockResolvedValueOnce({
      status: 403,
      headers: new Map([['Content-Type', 'application/json']]),
      json() {
        return Promise.resolve({ message: 'forbidden' })
      },
      clone: () => ({}),
    })
    .mockResolvedValueOnce({
      status: 500,
      headers: new Map([['Content-Type', 'application/json']]),
      json() {
        return Promise.resolve({
          message: 'fake exception',
          exception: true,
          trace: [
            { file: 'k.php', line: 2 },
            { file: 'v.php', line: 3 },
          ],
        })
      },
      clone: () => ({}),
    })

  const result: {
    code: number,
    message: string
  } = await net.walkFetch({ headers: new Headers() } as Request)
  expect(result.code).toBe(1)
  expect(result.message).toBe('required')

  await net.walkFetch({ headers: new Headers() } as Request)
  expect(showModal).toBeCalledWith('forbidden', undefined, 'warning')

  await net.walkFetch({ headers: new Headers() } as Request)
  expect(showAjaxError.mock.calls[0][0].message).toBe(
    'fake exception\n<details>[1] k.php#L2\n[2] v.php#L3</details>'
  )
})

test('inject to Vue instance', () => {
  expect(typeof Vue.prototype.$http.get).toBe('function')
  expect(typeof Vue.prototype.$http.post).toBe('function')
})
