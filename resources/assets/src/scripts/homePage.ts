import './i18n'
import { getExtraData } from './extra'

export function scrollHander() {
  const header = document.querySelector('.navbar')
  /* istanbul ignore else */
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY >= (window.innerHeight * 2) / 3) {
        header.classList.remove('transparent')
      } else {
        header.classList.add('transparent')
      }
    })
  }
}

export async function logout() {
  await fetch(`${blessing.base_url}/auth/logout`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
      )!.content,
    },
  })
  window.location.href = blessing.base_url
}

/* istanbul ignore next */
if (process.env.NODE_ENV !== 'test') {
  const { transparent_navbar } = getExtraData() as {
    transparent_navbar: boolean
  }
  if (transparent_navbar) {
    window.addEventListener('load', scrollHander)
  }
}
/* istanbul ignore next */
document
  .querySelector<HTMLButtonElement>('#btn-logout')
  ?.addEventListener('click', logout)
