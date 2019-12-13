const chooser = document.querySelector<HTMLInputElement>('#language-chooser')
chooser?.addEventListener('change', () => {
  window.location.href = `?lang=${chooser.value}`
})
