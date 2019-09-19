const chooser: HTMLInputElement | null = document.querySelector('#language-chooser')
if (chooser) {
  chooser.addEventListener('change', () => {
    window.location.href = `?lang=${chooser.value}`
  })
}
