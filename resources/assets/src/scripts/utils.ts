export function debounce(func: CallableFunction, delay: number) {
  let timer: number
  return () => {
    clearTimeout(timer)
    timer = setTimeout(func, delay)
  }
}

export function queryString(key: string, defaultValue: string = ''): string {
  const result = new RegExp(`[?&]${key}=([^&]+)`, 'i').exec(location.search)

  if (result === null || result.length < 1) {
    return defaultValue
  }
  return result[1]
}

export function queryStringify(params: { [key: string]: string }): string {
  return Object
    .keys(params)
    .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
    .join('&')
}
