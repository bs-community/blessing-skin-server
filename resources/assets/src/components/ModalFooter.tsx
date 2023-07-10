export interface Props {
  flexFooter?: boolean
  okButtonText?: string
  okButtonType?: string
  cancelButtonText?: string
  cancelButtonType?: string
}

interface InternalProps {
  showCancelButton: boolean
  onConfirm?(): void
  onDismiss?(): void
}

const ModalFooter: React.FC<InternalProps & Props> = (props) => {
  const classes = ['modal-footer']
  if (props.flexFooter) {
    classes.push('d-flex', 'justify-content-between')
  }
  const footerClass = classes.join(' ')

  return props.children ? (
    <div className={footerClass}>{props.children}</div>
  ) : (
    <div className={footerClass}>
      {props.showCancelButton && (
        <button
          type="button"
          className={`btn btn-${props.cancelButtonType}`}
          data-dismiss="modal"
          onClick={props.onDismiss}
        >
          {props.cancelButtonText}
        </button>
      )}
      <button
        type="button"
        className={`btn btn-${props.okButtonType}`}
        onClick={props.onConfirm}
      >
        {props.okButtonText}
      </button>
    </div>
  )
}

export default ModalFooter
