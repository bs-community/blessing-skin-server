import type { Paginator } from '@/scripts/types'

export function flushPromises() {
  return new Promise((resolve) => setImmediate(resolve))
}

export function createPaginator<T>(data: T[], perPage = 6): Paginator<T> {
  return {
    data,
    total: data.length,
    from: 1,
    to: data.length,
    current_page: 1,
    last_page: Math.ceil(data.length / perPage),
  }
}
