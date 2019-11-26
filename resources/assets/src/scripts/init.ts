/* eslint-disable prefer-const */
/* eslint-disable @typescript-eslint/camelcase */

import $ from 'jquery'

// @ts-ignore
declare let __webpack_public_path__: string

const url = new URL(document.baseURI)
url.port = '8080'

__webpack_public_path__ = process.env.NODE_ENV === 'development'
  ? url.toString()
  : `${document.baseURI}app/`

// @ts-ignore
window.$ = window.jQuery = $ // eslint-disable-line
