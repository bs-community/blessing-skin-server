import React from 'react'

interface Props {
  show?: boolean
  title?: string
  onDismiss?(): void
}

const ModalHeader: React.FC<Props> = props =>
  props.show ? (
    <div className="modal-header">
      <h5 className="modal-title">{props.title}</h5>
      <button
        type="button"
        className="close"
        data-dismiss="modal"
        aria-label="Close"
        onClick={props.onDismiss}
      >
        <span aria-hidden>&times;</span>
      </button>
    </div>
  ) : null

export default ModalHeader
