import React, {useState, useEffect, useRef} from 'react';
import $ from 'jquery';
import 'bootstrap';
import {t} from '../scripts/i18n';
import ModalHeader, {type Props as HeaderProperties} from './ModalHeader';
import ModalBody, {type Props as BodyProperties} from './ModalBody';
import ModalFooter, {type Props as FooterProperties} from './ModalFooter';

type BasicOptions = {
	readonly mode?: 'alert' | 'confirm' | 'prompt';
	readonly show?: boolean;
	readonly input?: string;
	validator?(value: any): string | boolean | undefined;
	readonly type?: string;
	readonly showHeader?: boolean;
	readonly center?: boolean;
	children?: React.ReactNode;
};

export type ModalOptions = BasicOptions & HeaderProperties & BodyProperties & FooterProperties;

type Properties = {
	readonly id?: string;
	readonly children?: React.ReactNode;
	readonly footer?: React.ReactNode;
	onConfirm?(payload: {value: string}): void;
	onDismiss?(): void;
	onClose?(): void;
};

export type ModalResult = {
	value: string;
};

const Modal: React.FC<ModalOptions & Properties> = properties => {
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
	} = properties;

	const [value, setValue] = useState(input);
	const [valid, setValid] = useState(true);
	const [validatorMessage, setValidatorMessage] = useState('');
	const reference = useRef<HTMLDivElement>(null);

	const {show, onClose} = properties;

	useEffect(() => {
		if (!show) {
			return;
		}

		const onHidden = () => {
			onClose ? onClose() : void 0;
		};

		const element = $(reference.current!);
		element.on('hidden.bs.modal', onHidden);

		return () => {
			element.off('hidden.bs.modal', onHidden);
		};
	}, [show, onClose]);

	const handleInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		setValue(event.target.value);
	};

	const confirm = () => {
		const {validator} = properties;
		if (typeof validator === 'function') {
			const result = validator(value);
			if (typeof result === 'string') {
				setValidatorMessage(result);
				setValid(false);
				return;
			}
		}

		properties.onConfirm?.({value});
		$(reference.current!).modal('hide');

		// The "hidden.bs.modal" event can't be trigged automatically when testing.
		/* istanbul ignore next */
		if (process.env.NODE_ENV === 'test') {
			$(reference.current!).trigger('hidden.bs.modal');
		}
	};

	const dismiss = () => {
		properties.onDismiss?.();
		$(reference.current!).modal('hide');

		/* istanbul ignore next */
		if (process.env.NODE_ENV === 'test') {
			$(reference.current!).trigger('hidden.bs.modal');
		}
	};

	useEffect(() => {
		if (show) {
			setTimeout(() => $(reference.current!).modal('show'), 50);
		}
	}, [show]);

	if (!show) {
		return null;
	}

	return (
		<div ref={reference} id={properties.id} className='modal fade' role='dialog'>
			<div
				className={`modal-dialog ${center ? 'modal-dialog-centered' : ''}`}
				role='document'
			>
				<div className={`modal-content bg-${type}`}>
					<ModalHeader show={showHeader} title={title} onDismiss={dismiss}/>
					<ModalBody
						text={text}
						dangerousHTML={properties.dangerousHTML}
						showInput={mode === 'prompt'}
						value={value}
						choices={properties.choices}
						inputType={inputType}
						inputMode={inputMode}
						placeholder={placeholder}
						invalid={!valid}
						validatorMessage={validatorMessage}
						onChange={handleInputChange}
					>
						{properties.children}
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
						{properties.footer}
					</ModalFooter>
				</div>
			</div>
		</div>
	);
};

export default Modal;
