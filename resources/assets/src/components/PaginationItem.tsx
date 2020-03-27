import React from 'react'

interface Props {
  disabled?: boolean
  active?: boolean
  onClick?(): void
}

const PaginationItem: React.FC<Props> = (props) => {
  const classes = ['page-item']
  if (props.active) {
    classes.push('active')
  }
  if (props.disabled) {
    classes.push('disabled')
  }

  const handleClick = (event: React.MouseEvent) => {
    event.preventDefault()
    if (!props.disabled && props.onClick) {
      props.onClick()
    }
  }

  return (
    <li className={classes.join(' ')} onClick={handleClick}>
      <a href="#" className="page-link" aria-disabled={props.disabled}>
        {props.children}
      </a>
    </li>
  )
}

export default PaginationItem
