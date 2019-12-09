import { showModal } from '../../scripts/notify'

export default function (message: string, reason: string[]): void {
  const div = document.createElement('div')
  const p = document.createElement('p')
  p.textContent = message
  div.appendChild(p)
  const ul = document.createElement('ul')
  reason.forEach(item => {
    const li = document.createElement('li')
    li.textContent = item
    ul.appendChild(li)
  })
  div.appendChild(ul)
  showModal({
    mode: 'alert',
    dangerousHTML: div.outerHTML,
  })
}
