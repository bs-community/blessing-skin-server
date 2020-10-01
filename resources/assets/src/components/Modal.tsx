import React, { useState, useEffect, useRef } from 'react'
import $ from 'jquery'
import 'bootstrap'
import { t } from '../scripts/i18n'
import ModalHeader from './ModalHeader'
import ModalBody from './ModalBody'
import ModalFooter from './ModalFooter'
import type { Props as HeaderProps } from './ModalHeader'
import type { Props as BodyProps } from './ModalBody'
import type { Props as FooterProps } from './ModalFooter'

type BasicOptions = {
  mode?: 'alert' | 'confirm' | 'prompt'
  show?: boolean
  input?: string
  validator?(value: any): string | boolean | undefined
  type?: string
  showHeader?: boolean
  center?: boolean
  children?: React.ReactNode
}

export type ModalOptions = BasicOptions & HeaderProps & BodyProps & FooterProps

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

const Modal: React.FC<ModalOptions & Props> = (props) => {
  const {
    mode = 'confirm',
    title = t('general.tip'),
    text = '',
    input = '',
    placeholder = '',
    inputType = 'text',
    inputMode,
    type = 'default',
    showHeader = true,
    center = false,
    okButtonText = t('general.confirm'),
    okButtonType = 'primary',
    cancelButtonText = t('general.cancel'),
    cancelButtonType = 'secondary',
    flexFooter = false,
  } = props

  const [value, setValue] = useState(input)
  const [valid, setValid] = useState(true)
  const [validatorMessage, setValidatorMessage] = useState('')
  const ref = useRef<HTMLDivElement>(null)

  const { show } = props

  useEffect(() => {
    if (!show) {
      return
    }

    const onHidden = () => props.onClose?.()

    const el = $(ref.current!)
    el.on('hidden.bs.modal', onHidden)

    return () => {
      el.off('hidden.bs.modal', onHidden)
    }
  }, [show, props.onClose])

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
    if (show) {
      setTimeout(() => $(ref.current!).modal('show'), 50)
    }
  }, [show])

  if (!show) {
    return null
  }

  return (
    <div id={props.id} className="modal fade" role="dialog" ref={ref}>
      <div
        className={`modal-dialog ${center ? 'modal-dialog-centered' : ''}`}
        role="document"
      >
        <div className={`modal-content bg-${type}`}>
          <ModalHeader show={showHeader} title={title} onDismiss={dismiss} />
          <ModalBody
            text={text}
            dangerousHTML={props.dangerousHTML}
            showInput={mode === 'prompt'}
            value={value}
            choices={props.choices}
            onChange={handleInputChange}
            inputType={inputType}
            inputMode={inputMode}
            placeholder={placeholder}
            invalid={!valid}
            validatorMessage={validatorMessage}
          >
            {props.children}
          </ModalBody>
          <ModalFooter
            showCancelButton={mode !== 'alert'}
            flexFooter={flexFooter}
            okButtonType={okButtonType}
            okButtonText={okButtonText}
            cancelButtonType={cancelButtonType}
            cancelButtonText={cancelButtonText}
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

export default Modal
