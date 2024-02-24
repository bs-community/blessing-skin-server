import type React, {type HTMLAttributes} from 'react';

export type Props = {
	readonly inputType?: string;
	readonly inputMode?: HTMLAttributes<HTMLInputElement>['inputMode'];
	readonly choices?: Array<{text: string; value: string}>;
	readonly placeholder?: string;
};

export type InternalProps = {
	readonly value?: string;
	readonly invalid?: boolean;
	readonly validatorMessage?: string;
	readonly onChange?: React.ChangeEventHandler<HTMLInputElement>;
};

const ModalInput: React.FC<InternalProps & Props> = properties => (
	<>
		{properties.inputType === 'radios' && properties.choices ? (
			<>
				{properties.choices.map(choice => (
					<div key={choice.value}>
						<input
							type='radio'
							name='modal-radios'
							id={`modal-radio-${choice.value}`}
							value={choice.value}
							checked={choice.value === properties.value}
							onChange={properties.onChange}
						/>
						<label htmlFor={`modal-radio-${choice.value}`} className='ml-1'>
							{choice.text}
						</label>
					</div>
				))}
			</>
		) : (
			<div className='form-group'>
				<input
					value={properties.value}
					type={properties.inputType}
					inputMode={properties.inputMode}
					className='form-control'
					placeholder={properties.placeholder}
					onChange={properties.onChange}
				 />
			</div>
		)}
		{properties.invalid && (
			<div className='alert alert-danger'>
				<i className='icon far fa-times-circle'/>
				<span className='ml-1'>{properties.validatorMessage}</span>
			</div>
		)}
	</>
);

export default ModalInput;
