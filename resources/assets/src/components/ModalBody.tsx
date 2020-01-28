import React from 'react'
import ModalContent from './ModalContent'
import ModalInput from './ModalInput'

interface Props {
  text?: string
  dangerousHTML?: string
  showInput: boolean
  inputType?: string
  value?: string
  choices?: { text: string; value: string }[]
  onChange?: React.ChangeEventHandler<HTMLInputElement>
  placeholder?: string
  invalid?: boolean
  validatorMessage?: string
}

const ModalBody: React.FC<Props> = props => {
  return (
    <div className="modal-body">
      <ModalContent text={props.text} dangerousHTML={props.dangerousHTML}>
        {props.children}
      </ModalContent>
      {props.showInput && <ModalInput {...props} />}
    </div>
  )
}

export default ModalBody
