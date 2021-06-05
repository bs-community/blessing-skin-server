const blessingElement = document.querySelector('#blessing-globals')!
// @ts-ignore
window.blessing = JSON.parse(blessingElement.textContent!)

window.addEventListener('load', () => {
  navigator.serviceWorker.register('/sw.js?v6')
})

export {}
