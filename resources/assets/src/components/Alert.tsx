import React from 'react'

type AlertType = 'success' | 'info' | 'warning' | 'danger'

const icons = new Map<AlertType, string>([
  ['success', 'check'],
  ['info', 'info'],
  ['warning', 'exclamation-triangle'],
  ['danger', 'times-circle'],
])

interface Props {
  type: AlertType
}

const Alert: React.FC<Props> = (props) => {
  const { type } = props
  const icon = icons.get(type)

  return props.children ? (
    <div className={`alert alert-${type}`}>
      <i className={`icon fas fa-${icon}`}></i>
      {props.children}
    </div>
  ) : null
}

export default Alert
