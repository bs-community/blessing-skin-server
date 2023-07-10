import { getExtraData } from './extra'

export function scrollHander() {
  const header = document.querySelector('.navbar')

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

if (process.env.NODE_ENV !== 'test') {
  const { transparent_navbar } = getExtraData() as {
    transparent_navbar: boolean
  }
  if (transparent_navbar) {
    window.addEventListener('load', scrollHander)
  }
}
