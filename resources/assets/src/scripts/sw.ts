import { registerRoute } from 'workbox-routing'
import {
  CacheFirst,
  StaleWhileRevalidate,
  NetworkOnly,
} from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'

const oneWeek = 7 * 24 * 3600

if (process.env.NODE_ENV === 'development') {
  registerRoute(/\.js/, new NetworkOnly())
  registerRoute(/\.css/, new NetworkOnly())
}

//#region Pictures
registerRoute(
  /\/preview\/\d+/,
  new CacheFirst({
    cacheName: 'texture-preview-v2',
    fetchOptions: {
      credentials: 'omit',
    },
    plugins: [
      new ExpirationPlugin({ maxAgeSeconds: oneWeek, purgeOnQuotaError: true }),
    ],
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
  /\/avatar\/\d+/,
  new CacheFirst({
    cacheName: 'avatar-v2',
    fetchOptions: {
      credentials: 'omit',
    },
    plugins: [new ExpirationPlugin({ maxAgeSeconds: oneWeek })],
  }),
)

//#endregion

//#region JavaScript files
registerRoute(
  /.+\/app\/\w{2,3}\.\w{7}\.js$/,
  new CacheFirst({
    cacheName: 'javascript-v1',
    fetchOptions: {
      credentials: 'omit',
      mode: 'cors',
    },
    plugins: [
      new ExpirationPlugin({ maxAgeSeconds: oneWeek, purgeOnQuotaError: true }),
    ],
  }),
)
registerRoute(
  /.+\/plugins\/.+\.js$/,
  new StaleWhileRevalidate({
    cacheName: 'javascript-v1',
    fetchOptions: {
      credentials: 'omit',
      mode: 'cors',
    },
    plugins: [
      new ExpirationPlugin({ maxAgeSeconds: oneWeek, purgeOnQuotaError: true }),
    ],
  }),
)
//#endregion

//#region CSS files
registerRoute(
  /.+\/app\/.*\.css$/,
  new CacheFirst({
    cacheName: 'stylesheet-v1',
    fetchOptions: {
      credentials: 'omit',
      mode: 'cors',
    },
    plugins: [
      new ExpirationPlugin({ maxAgeSeconds: oneWeek, purgeOnQuotaError: true }),
    ],
  }),
)
registerRoute(
  /.+\/plugins\/.+\.css$/,
  new StaleWhileRevalidate({
    cacheName: 'stylesheet-v1',
    fetchOptions: {
      credentials: 'omit',
      mode: 'cors',
    },
    plugins: [
      new ExpirationPlugin({ maxAgeSeconds: oneWeek, purgeOnQuotaError: true }),
    ],
  }),
)
//#endregion

//#region Fonts
registerRoute(
  ({ request }) => request.destination === 'font',
  new StaleWhileRevalidate({
    cacheName: 'font-v1',
    fetchOptions: {
      credentials: 'omit',
      mode: 'cors',
    },
    plugins: [new ExpirationPlugin({ maxEntries: 12 })],
  }),
)
//#endregion
