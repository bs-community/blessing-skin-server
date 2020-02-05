import React, { useState, useEffect } from 'react'
import styles from './Toast.scss'

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
    setTimeout(() => setShow(true), 100)
    setTimeout(() => setShow(false), 3000)
    setTimeout(props.onClose, 3100)
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
        <span className="mr-auto">
          <i className={`icon fas fa-${icons.get(props.type)}`}></i>
          <span>{props.children}</span>
        </span>
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

Toast.displayName = 'Toast'

export default Toast
