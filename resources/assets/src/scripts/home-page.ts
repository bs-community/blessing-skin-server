function handler() {
  const header = document.querySelector('.navbar')
  /* istanbul ignore else */
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY >= window.innerHeight * 2 / 3) {
        header.classList.remove('transparent')
      } else {
        header.classList.add('transparent')
      }
    })
  }
}

/* istanbul ignore next */
if (blessing.extra.transparent_navbar) {
  window.addEventListener('load', handler)
}

export default handler
