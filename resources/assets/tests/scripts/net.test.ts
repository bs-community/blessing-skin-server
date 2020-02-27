import Vue from 'vue'
import * as net from '@/scripts/net'
import { on } from '@/scripts/event'
import { trans, t } from '@/scripts/i18n'
import { showModal } from '@/scripts/notify'

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

test('the PUT method', () => {
  const fetch = jest.fn()
  window.fetch = fetch

  const stub = jest.fn()
  on('beforeFetch', stub)

  net.put('/abc')
  expect(fetch).toBeCalled()
  // eslint-disable-next-line prefer-destructuring
  const request = fetch.mock.calls[0][0]
  expect(request.method).toBe('PUT')

  expect(stub).toBeCalledWith({
    method: 'PUT',
    url: '/abc',
    data: {},
  })
})

test('the DELETE method', () => {
  const fetch = jest.fn()
  window.fetch = fetch

  const stub = jest.fn()
  on('beforeFetch', stub)

  net.del('/abc')
  expect(fetch).toBeCalled()
  // eslint-disable-next-line prefer-destructuring
  const request = fetch.mock.calls[0][0]
  expect(request.method).toBe('DELETE')

  expect(stub).toBeCalledWith({
    method: 'DELETE',
    url: '/abc',
    data: {},
  })
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
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    title: trans('general.fatalError'),
    dangerousHTML: 'network',
    type: 'danger',
    okButtonType: 'outline-light',
  })
  expect(stub).toBeCalledWith(expect.any(Error))

  await net.walkFetch(request as Request)
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    title: trans('general.fatalError'),
    dangerousHTML: '404',
    type: 'danger',
    okButtonType: 'outline-light',
  })
  expect(stub.mock.calls[1][0]).toHaveProperty('message', '404')
  expect(stub.mock.calls[1][0]).toHaveProperty('response')

  await net.walkFetch(request as Request)
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    title: trans('general.fatalError'),
    dangerousHTML: 'error',
    type: 'danger',
    okButtonType: 'outline-light',
  })
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
      status: 419,
      headers: new Map([['Content-Type', 'application/json']]),
      json() {
        return Promise.resolve({
          message: 'CSRF token mismatched.',
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
    code: number
    message: string
  } = await net.walkFetch({ headers: new Headers() } as Request)
  expect(result.code).toBe(1)
  expect(result.message).toBe('required')

  await net.walkFetch({ headers: new Headers() } as Request)
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    text: t('general.csrf'),
  })

  await net.walkFetch({ headers: new Headers() } as Request)
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    text: 'forbidden',
    type: 'warning',
  })

  await net.walkFetch({ headers: new Headers() } as Request)
  expect(showModal).toBeCalledWith({
    mode: 'alert',
    title: trans('general.fatalError'),
    dangerousHTML: 'fake exception<br><details>[1] k.php#L2<br>[2] v.php#L3</details>',
    type: 'danger',
    okButtonType: 'outline-light',
  })
})

test('inject to Vue instance', () => {
  expect(typeof Vue.prototype.$http.get).toBe('function')
  expect(typeof Vue.prototype.$http.post).toBe('function')
})
