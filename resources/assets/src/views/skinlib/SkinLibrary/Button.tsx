interface Props {
  active?: boolean
  bg?: string
}

type Attributes = React.DetailedHTMLProps<
  React.ButtonHTMLAttributes<HTMLButtonElement>,
  HTMLButtonElement
>

const Button: React.FC<Props & Attributes> = (props) => {
  const classes = [props.className ?? '']
  if (props.bg) {
    classes.push('btn', `bg-${props.bg}`)
  }
  if (props.active) {
    classes.push('active')
  }

  const rest = { ...props, active: undefined, bg: undefined }

  return (
    <button {...rest} className={classes.join(' ')}>
      {props.children}
    </button>
  )
}

export default Button
