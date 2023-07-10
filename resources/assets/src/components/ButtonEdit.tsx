interface Props {
  title?: string
  onClick: React.MouseEventHandler<HTMLAnchorElement>
}

const ButtonEdit: React.FC<Props> = (props) => (
  <a href="#" title={props.title} className="ml-2" onClick={props.onClick}>
    <i className="fas fa-edit"></i>
  </a>
)

export default ButtonEdit
