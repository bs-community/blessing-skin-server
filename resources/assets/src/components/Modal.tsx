import React, { useState, useEffect, useRef } from 'react'
import $ from 'jquery'
import 'bootstrap'
import { trans } from '../scripts/i18n'
import ModalHeader from './ModalHeader'
import ModalBody from './ModalBody'
import ModalFooter from './ModalFooter'

export type ModalOptions = {
  mode?: 'alert' | 'confirm' | 'prompt'
  show?: boolean
  title?: string
  text?: string
  dangerousHTML?: string
  input?: string
  placeholder?: string
  inputType?: string
  validator?(value: any): string | boolean | undefined
  choices?: { text: string; value: string }[]
  type?: string
  showHeader?: boolean
  center?: boolean
  okButtonText?: string
  okButtonType?: string
  cancelButtonText?: string
  cancelButtonType?: string
  flexFooter?: boolean
}

type Props = {
  id?: string
  children?: React.ReactNode
  footer?: React.ReactNode
  onConfirm?(payload: { value: string }): void
  onDismiss?(): void
  onClose?(): void
}

export type ModalResult = {
  value: string
}

const Modal: React.FC<ModalOptions & Props> = props => {
  const [value, setValue] = useState(props.input!)
  const [valid, setValid] = useState(true)
  const [validatorMessage, setValidatorMessage] = useState('')
  const ref = useRef<HTMLDivElement>(null)

  const handleInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setValue(event.target.value)
  }

  const confirm = () => {
    const { validator } = props
    if (typeof validator === 'function') {
      const result = validator(value)
      if (typeof result === 'string') {
        setValidatorMessage(result)
        setValid(false)
        return
      }
    }

    props.onConfirm?.({ value })
    $(ref.current!).modal('hide')

    // The "hidden.bs.modal" event can't be trigged automatically when testing.
    /* istanbul ignore next */
    if (process.env.NODE_ENV === 'test') {
      $(ref.current!).trigger('hidden.bs.modal')
    }
  }

  const dismiss = () => {
    props.onDismiss?.()
    $(ref.current!).modal('hide')

    /* istanbul ignore next */
    if (process.env.NODE_ENV === 'test') {
      $(ref.current!).trigger('hidden.bs.modal')
    }
  }

  useEffect(() => {
    if (!props.show) {
      return
    }

    const onHidden = () => props.onClose?.()

    const el = $(ref.current!)
    el.on('hidden.bs.modal', onHidden)

    return () => {
      el.off('hidden.bs.modal', onHidden)
    }
  }, [props.onClose, props.show])

  useEffect(() => {
    if (props.show) {
      setTimeout(() => $(ref.current!).modal('show'), 50)
    }
  }, [props.show])

  if (!props.show) {
    return null
  }

  return (
    <div
      id={props.id}
      className="modal fade"
      tabIndex={-1}
      role="dialog"
      aria-hidden={!props.show}
      ref={ref}
    >
      <div
        className={`modal-dialog ${
          props.center ? 'modal-dialog-centered' : ''
        }`}
        role="document"
      >
        <div className={`modal-content bg-${props.type}`}>
          <ModalHeader
            show={props.showHeader}
            title={props.title}
            onDismiss={dismiss}
          />
          <ModalBody
            text={props.text}
            dangerousHTML={props.dangerousHTML}
            showInput={props.mode === 'prompt'}
            value={value}
            choices={props.choices}
            onChange={handleInputChange}
            inputType={props.inputType}
            placeholder={props.placeholder}
            invalid={!valid}
            validatorMessage={validatorMessage}
          >
            {props.children}
          </ModalBody>
          <ModalFooter
            showCancelButton={props.mode !== 'alert'}
            flexFooter={props.flexFooter}
            okButtonType={props.okButtonType}
            okButtonText={props.okButtonText}
            cancelButtonType={props.cancelButtonType}
            cancelButtonText={props.cancelButtonText}
            onConfirm={confirm}
            onDismiss={dismiss}
          >
            {props.footer}
          </ModalFooter>
        </div>
      </div>
    </div>
  )
}

Modal.displayName = 'Modal'

Modal.defaultProps = {
  mode: 'confirm',
  title: trans('general.tip'),
  text: '',
  input: '',
  placeholder: '',
  inputType: 'text',
  type: 'default',
  showHeader: true,
  center: false,
  okButtonText: trans('general.confirm'),
  okButtonType: 'primary',
  cancelButtonText: trans('general.cancel'),
  cancelButtonType: 'secondary',
  flexFooter: false,
}

export default Modal
