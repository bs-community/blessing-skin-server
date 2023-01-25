const blessingElement = document.querySelector('#blessing-globals')!
// @ts-ignore
window.blessing = JSON.parse(blessingElement.textContent!)

export {}
