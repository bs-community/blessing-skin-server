import { get } from './net'
import { showModal } from './notify'

export default async function handler(event: Event) {
  const item = event.target as HTMLAnchorElement
  const id = item.getAttribute('data-nid')
  const {
    title, content, time,
  }: {
    title: string
    content: string
    time: string
  } = await get(`/user/notifications/${id!}`)
  showModal(`${content}<br><small>${time}</small>`, title)
  item.remove()
  const counter = document
    .querySelector('.notifications-counter') as HTMLSpanElement
  const value = Number.parseInt(counter.textContent!) - 1
  if (value > 0) {
    counter.textContent = value.toString()
  } else {
    counter.remove()
  }
}

const el = document.querySelector('.notifications-list')
// istanbul ignore next
if (el) {
  el.addEventListener('click', handler)
}
