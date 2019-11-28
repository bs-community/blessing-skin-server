import Vue from 'vue'
import { emit } from './event'
import { queryStringify } from './utils'
import { showModal } from './notify'
import { trans } from './i18n'

class HTTPError extends Error {
  response: Response

  constructor(message: string, response: Response) {
    super(message)
    this.response = response
  }
}

const empty = Object.create(null)
export const init: RequestInit = {
  credentials: 'same-origin',
  headers: new Headers({
    Accept: 'application/json',
  }),
}

function retrieveToken() {
  const csrfField: HTMLMetaElement | null =
    document.querySelector('meta[name="csrf-token"]')
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
  return (csrfField && csrfField.content) || ''
}

export async function walkFetch(request: Request): Promise<any> {
  request.headers.set('X-CSRF-TOKEN', retrieveToken())

  try {
    const response = await fetch(request)
    const cloned = response.clone()
    const body = response.headers.get('Content-Type') === 'application/json'
      ? await response.json()
      : await response.text()
    if (response.ok) {
      return body
    }
    let message: string = body.message

    if (response.status === 422) {
      // Process validation errors from Laravel.
      const { errors }: {
        message: string
        errors: { [field: string]: string[] }
      } = body
      return {
        code: 1,
        message: Object.keys(errors).map(field => errors[field][0])[0],
      }
    } else if (response.status === 403) {
      showModal({
        mode: 'alert',
        text: message,
        type: 'warning',
      })
      return
    }

    if (body.exception && Array.isArray(body.trace)) {
      const trace = (body.trace as Array<{ file: string, line: number }>)
        .map((t, i) => `[${i + 1}] ${t.file}#L${t.line}`)
        .join('\n')
      message = `${message}\n<details>${trace}</details>`
    }

    throw new HTTPError(message || body, cloned)
  } catch (error) {
    emit('fetchError', error)
    showModal({
      mode: 'alert',
      title: trans('general.fatalError'),
      dangerousHTML: error.message,
      type: 'danger',
    })
  }
}

export function get(url: string, params = empty): Promise<any> {
  emit('beforeFetch', {
    method: 'GET',
    url,
    data: params,
  })

  const qs = queryStringify(params)

  return walkFetch(new Request(`${blessing.base_url}${url}${qs && `?${qs}`}`, init))
}

function nonGet(method: string, url: string, data: any): Promise<any> {
  emit('beforeFetch', {
    method: method.toUpperCase(),
    url,
    data,
  })

  const isFormData = data instanceof FormData

  const request = new Request(`${blessing.base_url}${url}`, {
    body: isFormData ? data : JSON.stringify(data),
    method: method.toUpperCase(),
    ...init,
  })
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
  !isFormData && request.headers.set('Content-Type', 'application/json')

  return walkFetch(request)
}

export function post(url: string, data = empty): Promise<any> {
  return nonGet('POST', url, data)
}

export function put(url: string, data = empty): Promise<any> {
  return nonGet('PUT', url, data)
}

export function del(url: string, data = empty): Promise<any> {
  return nonGet('DELETE', url, data)
}

Vue.use(_Vue => {
  Object.defineProperty(_Vue.prototype, '$http', {
    get: () => ({
      get, post, put, del,
    }),
  })
})

blessing.fetch = {
  get, post, put, del,
}
