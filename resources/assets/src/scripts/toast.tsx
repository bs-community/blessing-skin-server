import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom'
import nanoid from 'nanoid'
import * as emitter from './event'
import ToastBox, { ToastType } from '../components/Toast'

type QueueElement = { id: string; type: ToastType; message: string }
type ToastQueue = QueueElement[]

const TOAST_EVENT = Symbol('toast')
const CLEAR_EVENT = Symbol('clear')

export const ToastContainer: React.FC = () => {
  const [queue, setQueue] = useState<ToastQueue>([])

  useEffect(() => {
    const off1 = emitter.on(TOAST_EVENT, (toast: QueueElement) => {
      setQueue((queue) => {
        queue.push(toast)
        return queue.slice()
      })
    })
    const off2 = emitter.on(CLEAR_EVENT, () => setQueue([]))

    return () => {
      off1()
      off2()
    }
  }, [])

  const handleClose = (id: string) => {
    setQueue((queue) => queue.filter((el) => el.id !== id))
  }

  return (
    <>
      {queue.map((el, i) => (
        <ToastBox
          key={el.id}
          type={el.type}
          distance={50 + i * 70}
          onClose={() => handleClose(el.id)}
        >
          {el.message}
        </ToastBox>
      ))}
    </>
  )
}

export class Toast {
  constructor() {
    const container = document.createElement('div')
    document.body.appendChild(container)

    ReactDOM.render(<ToastContainer />, container)
  }

  success(message: string) {
    emitter.emit(TOAST_EVENT, { id: nanoid(4), type: 'success', message })
  }

  info(message: string) {
    emitter.emit(TOAST_EVENT, { id: nanoid(4), type: 'info', message })
  }

  warning(message: string) {
    emitter.emit(TOAST_EVENT, { id: nanoid(4), type: 'warning', message })
  }

  error(message: string) {
    emitter.emit(TOAST_EVENT, { id: nanoid(4), type: 'error', message })
  }

  clear() {
    emitter.emit(CLEAR_EVENT)
  }
}
