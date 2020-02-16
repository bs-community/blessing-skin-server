import React from 'react'

interface Props {
  name: string
  icon: string
  color: string
  used: number
  total: number
  unit: string
}

const InfoBox: React.FC<Props> = props => {
  const percentage = (props.used / props.total) * 100

  return (
    <div className={`info-box bg-${props.color}`}>
      <span className="info-box-icon">
        <i className={`fas fa-${props.icon}`}></i>
      </span>
      <div className="info-box-content">
        <span className="info-box-text">{props.name}</span>
        <span className="info-box-number">
          <strong>{props.used}</strong> / {props.total} {props.unit}
        </span>
        <div className="progress">
          <div className="progress-bar" style={{ width: `${percentage}%` }} />
        </div>
      </div>
    </div>
  )
}

export default React.memo(InfoBox)
