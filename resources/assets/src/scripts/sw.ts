import { registerRoute } from 'workbox-routing'
import {
  CacheFirst,
  StaleWhileRevalidate,
  NetworkOnly,
} from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'

if (process.env.NODE_ENV === 'development') {
  registerRoute(/\.js/, new NetworkOnly())
  registerRoute(/\.css/, new NetworkOnly())
}

registerRoute(
  /\/preview\/\d+/,
  new CacheFirst({
    cacheName: 'texture-preview-v2',
    fetchOptions: {
      credentials: 'omit',
    },
    plugins: [new ExpirationPlugin({ maxAgeSeconds: 7 * 24 * 60 * 60 })],
  }),
)

registerRoute(
  /\/app\/.*\.webp/,
  new StaleWhileRevalidate({
    cacheName: 'webp-resource-v1',
    fetchOptions: {
      credentials: 'omit',
    },
  }),
)

registerRoute(
  /\/avatar\/user\/\d+/,
  new StaleWhileRevalidate({
    cacheName: 'avatar-v2',
    fetchOptions: {
      credentials: 'omit',
    },
  }),
)

registerRoute(/.+\/\d+\.js$/, new NetworkOnly())

registerRoute(
  /\/app\/\w{2,3}\.\w{7}\.js$/,
  new CacheFirst({
    cacheName: 'javascript-v1',
    fetchOptions: {
      credentials: 'omit',
    },
  }),
)

registerRoute(
  ({ request }) => request.destination === 'script',
  new StaleWhileRevalidate({
    cacheName: 'javascript-v1',
    fetchOptions: {
      credentials: 'omit',
    },
  }),
)
registerRoute(
  ({ request }) => request.destination === 'style',
  new StaleWhileRevalidate({
    cacheName: 'stylesheet-v1',
    fetchOptions: {
      credentials: 'omit',
    },
  }),
)
registerRoute(
  ({ request }) => request.destination === 'font',
  new StaleWhileRevalidate({
    cacheName: 'font-v1',
    fetchOptions: {
      credentials: 'omit',
    },
  }),
)
