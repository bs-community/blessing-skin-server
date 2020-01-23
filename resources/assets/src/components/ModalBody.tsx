import React from 'react'

interface Props {
  text?: string
  dangerousHTML?: string
  showInput: boolean
  inputType?: string
  value?: string
  onChange?: React.ChangeEventHandler<HTMLInputElement>
  placeholder?: string
  invalid?: boolean
  validatorMessage?: string
}

const ModalBody: React.FC<Props> = props => {
  const main = (() => {
    if (props.children) {
      return props.children
    } else if (props.text) {
      return props.text.split(/\r?\n/).map((line, i) => <p key={i}>{line}</p>)
    } else if (props.dangerousHTML) {
      return <div dangerouslySetInnerHTML={{ __html: props.dangerousHTML }} />
    }
  })()

  return (
    <div className="modal-body">
      {main}
      {props.showInput && (
        <>
          <div className="form-group">
            <input
              value={props.value}
              onChange={props.onChange}
              type={props.inputType}
              className="form-control"
              placeholder={props.placeholder}
            ></input>
          </div>
          {props.invalid && (
            <div className="alert alert-danger">
              <i className="icon far fa-times-circle"></i>
              <span className="ml-1">{props.validatorMessage}</span>
            </div>
          )}
        </>
      )}
    </div>
  )
}

export default ModalBody
