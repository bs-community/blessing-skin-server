import { emit } from './event'
import { showModal } from './notify'
import { t } from './i18n'

export interface ResponseBody<T = null> {
  code: number
  message: string
  data: T extends null ? never : T
}

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
  const csrfField = document.querySelector<HTMLMetaElement>(
    'meta[name="csrf-token"]',
  )
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
  return csrfField?.content || ''
}

export async function walkFetch(request: Request): Promise<any> {
  request.headers.set('X-CSRF-TOKEN', retrieveToken())

  try {
    const response = await fetch(request)
    const cloned = response.clone()
    const body =
      response.headers.get('Content-Type') === 'application/json'
        ? await response.json()
        : await response.text()
    if (response.ok) {
      return body
    }
    let message: string = body.message

    if (response.status === 422) {
      // Process validation errors from Laravel.
      const {
        errors,
      }: {
        message: string
        errors: { [field: string]: string[] }
      } = body
      return {
        code: 1,
        message: Object.keys(errors).map((field) => errors[field]![0])[0],
      }
    } else if (response.status === 419) {
      return showModal({
        mode: 'alert',
        text: t('general.csrf'),
      })
    } else if (response.status === 403 || response.status === 400) {
      return showModal({
        mode: 'alert',
        text: message,
        type: 'warning',
      })
    }

    if (body.exception && Array.isArray(body.trace)) {
      const trace = (body.trace as Array<{ file: string; line: number }>)
        .map((t, i) => `[${i + 1}] ${t.file}#L${t.line}`)
        .join('<br>')
      message = `${message}<br><details>${trace}</details>`
    }

    throw new HTTPError(message || body, cloned)
  } catch (error) {
    emit('fetchError', error)
    await showModal({
      mode: 'alert',
      title: t('general.fatalError'),
      dangerousHTML: error.message,
      type: 'danger',
      okButtonType: 'outline-light',
    })

    return { code: -1, message: t('general.fatalError') }
  }
}

export function get<T = any>(url: string, params = empty): Promise<T> {
  emit('beforeFetch', {
    method: 'GET',
    url,
    data: params,
  })

  const qs = new URLSearchParams(params).toString()

  return walkFetch(new Request(`${blessing.base_url}${url}?${qs}`, init))
}

function nonGet<T = any>(
  method: string,
  url: string,
  data?: FormData | Record<string, unknown>,
): Promise<T> {
  emit('beforeFetch', {
    method: method.toUpperCase(),
    url,
    data,
  })

  const request = new Request(`${blessing.base_url}${url}`, {
    body: data instanceof FormData ? data : JSON.stringify(data),
    method: method.toUpperCase(),
    ...init,
  })
  if (!(data instanceof FormData)) {
    request.headers.set('Content-Type', 'application/json')
  }

  return walkFetch(request)
}

export function post<T = any>(
  url: string,
  data?: FormData | Record<string, unknown>,
): Promise<T> {
  return nonGet<T>('POST', url, data)
}

export function put<T = any>(
  url: string,
  data?: FormData | Record<string, unknown>,
): Promise<T> {
  return nonGet<T>('PUT', url, data)
}

export function del<T = any>(
  url: string,
  data?: FormData | Record<string, unknown>,
): Promise<T> {
  return nonGet<T>('DELETE', url, data)
}

blessing.fetch = {
  get,
  post,
  put,
  del,
}
