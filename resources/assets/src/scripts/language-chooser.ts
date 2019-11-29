const chooser = document.querySelector<HTMLInputElement>('#language-chooser')
if (chooser) {
  chooser.addEventListener('change', () => {
    window.location.href = `?lang=${chooser.value}`
  })
}
