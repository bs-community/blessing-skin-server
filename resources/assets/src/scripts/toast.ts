import $ from 'jquery'
import 'bootstrap'

const toastIcons: Record<ToastType, string> = {
  success: 'check',
  info: 'info',
  warning: 'exclamation-triangle',
  error: 'times-circle',
}

export type ToastQueue = QueueElement[]
type QueueElement = { el: HTMLDivElement, height: number }
type ToastType =
  | 'success'
  | 'info'
  | 'warning'
  | 'error'

export function showToast(
  queue: ToastQueue,
  type: ToastType,
  message = '',
): void {
  const alertType = type === 'error' ? 'danger' : type

  const container = document.createElement('div')
  container.className = 'alert-toast'
  const last = queue[queue.length - 1]
  if (last) {
    container.style.top = `${last.el.offsetTop + last.el.offsetHeight + 12}px`
  } else {
    container.style.top = '35px'
  }

  const toast = document.createElement('div')
  toast.className = `alert alert-${alertType} d-flex justify-content-between fade`

  const icon = document.createElement('i')
  icon.className = `icon fas fa-${toastIcons[type]}`

  const text = document.createElement('span')
  text.textContent = message

  const span = document.createElement('span')
  span.className = 'mr-auto'
  span.appendChild(icon)
  span.appendChild(text)
  toast.appendChild(span)

  const button = document.createElement('button')
  button.type = 'button'
  button.className = 'ml-2 mr-1 close'
  button.dataset.dismiss = 'alert'
  button.textContent = '×'
  toast.appendChild(button)

  container.appendChild(toast)
  document.body.appendChild(container)
  queue.push({ el: container, height: container.offsetHeight })
  setTimeout(() => toast.classList.add('show'), 100)

  setTimeout(() => $(toast).alert('close'), 3000)
  $(toast).on('closed.bs.alert', () => {
    container.remove()
    let i = queue.findIndex(({ el }) => el === container)
    const distance = queue[i].height + 12
    for (i += 1; i < queue.length; i += 1) {
      const element = queue[i].el
      element.style.top = `${element.offsetTop - distance}px`
    }
  })
}

export class Toast {
  private queue: ToastQueue

  constructor() {
    this.queue = []
  }

  success(message = '') {
    return showToast(this.queue, 'success', message)
  }

  info(message = '') {
    return showToast(this.queue, 'info', message)
  }

  warning(message = '') {
    return showToast(this.queue, 'warning', message)
  }

  error(message = '') {
    return showToast(this.queue, 'error', message)
  }
}
