declare let __webpack_public_path__: string

if (process.env.NODE_ENV === 'development') {
  const url = new URL(blessing.base_url)
  url.port = '8080'
  url.pathname = '/app/'
  __webpack_public_path__ = url.toString()
} else {
  const link = document.querySelector<HTMLLinkElement>('link#cdn-host')
  const base = link?.href ?? blessing.base_url
  __webpack_public_path__ = `${base}/app/`
}

export {}
