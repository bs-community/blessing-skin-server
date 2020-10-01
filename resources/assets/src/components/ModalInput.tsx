import React, { HTMLAttributes } from 'react'

export interface Props {
  inputType?: string
  inputMode?: HTMLAttributes<HTMLInputElement>['inputMode']
  choices?: { text: string; value: string }[]
  placeholder?: string
}

export interface InternalProps {
  value?: string
  invalid?: boolean
  validatorMessage?: string
  onChange?: React.ChangeEventHandler<HTMLInputElement>
}

const ModalInput: React.FC<InternalProps & Props> = (props) => (
  <>
    {props.inputType === 'radios' && props.choices ? (
      <>
        {props.choices.map((choice) => (
          <div key={choice.value}>
            <input
              type="radio"
              name="modal-radios"
              id={`modal-radio-${choice.value}`}
              value={choice.value}
              checked={choice.value === props.value}
              onChange={props.onChange}
            />
            <label htmlFor={`modal-radio-${choice.value}`} className="ml-1">
              {choice.text}
            </label>
          </div>
        ))}
      </>
    ) : (
      <div className="form-group">
        <input
          value={props.value}
          onChange={props.onChange}
          type={props.inputType}
          inputMode={props.inputMode}
          className="form-control"
          placeholder={props.placeholder}
        ></input>
      </div>
    )}
    {props.invalid && (
      <div className="alert alert-danger">
        <i className="icon far fa-times-circle"></i>
        <span className="ml-1">{props.validatorMessage}</span>
      </div>
    )}
  </>
)

export default ModalInput
