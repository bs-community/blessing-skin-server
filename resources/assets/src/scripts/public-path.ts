/* eslint-disable prefer-const */
/* eslint-disable @typescript-eslint/camelcase */
declare let __webpack_public_path__: string

const url = new URL(blessing.base_url)
url.port = '8080'

__webpack_public_path__ = process.env.NODE_ENV === 'development'
  ? url.toString()
  : `${blessing.base_url}/app/`
