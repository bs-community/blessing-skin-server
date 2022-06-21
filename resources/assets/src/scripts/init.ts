declare let __webpack_public_path__: string
declare const __blessing_public_path__: string

if (process.env.NODE_ENV === 'development') {
  __webpack_public_path__ = __blessing_public_path__
} else {
  const link = document.querySelector<HTMLLinkElement>('link#cdn-host')
  const base = link?.href ?? blessing.base_url
  __webpack_public_path__ = `${base}/app/`
}

export {}
