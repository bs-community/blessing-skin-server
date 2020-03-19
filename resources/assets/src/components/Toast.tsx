import React, { useState, useEffect } from 'react'
import styles from './Toast.module.scss'

export type ToastType = 'success' | 'info' | 'warning' | 'error'

interface Props {
  type: ToastType
  distance: number
  onClose(): void | Promise<void>
}

const icons = new Map<ToastType, string>([
  ['success', 'check'],
  ['info', 'info'],
  ['warning', 'exclamation-triangle'],
  ['error', 'times-circle'],
])

const Toast: React.FC<Props> = props => {
  const [show, setShow] = useState(false)

  useEffect(() => {
    const id1 = setTimeout(() => setShow(true), 100)
    const id2 = setTimeout(() => setShow(false), 3000)
    const id3 = setTimeout(props.onClose, 3100)

    return () => {
      clearTimeout(id1)
      clearTimeout(id2)
      clearTimeout(id3)
    }
  }, [props.onClose])

  const type = props.type === 'error' ? 'danger' : props.type

  const classes = [
    `alert alert-${type}`,
    'd-flex justify-content-between',
    'fade',
    styles.shadow,
  ]
  if (show) {
    classes.push('show')
  }

  const role = type === 'success' || type === 'info' ? 'status' : 'alert'

  return (
    <div className={styles.toast} style={{ top: `${props.distance}px` }}>
      <div className={classes.join(' ')} role={role}>
        <span className="mr-1 d-flex align-items-center">
          <i className={`icon fas fa-${icons.get(props.type)}`}></i>
        </span>
        <span>{props.children}</span>
        <button
          type="button"
          className="mr-2 ml-1 close"
          onClick={props.onClose}
        >
          &times;
        </button>
      </div>
    </div>
  )
}

export default Toast
