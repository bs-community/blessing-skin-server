import {Modal as BootstrapModal} from 'bootstrap';
import clsx from 'clsx';
import {useEffect, useRef, useState} from 'react';
import {t} from '../scripts/i18n';
import ModalBody, {type Props as BodyProperties} from './ModalBody';
import ModalFooter, {type Props as FooterProperties} from './ModalFooter';
import ModalHeader, {type Props as HeaderProperties} from './ModalHeader';

type BasicOptions = {
	readonly mode?: 'alert' | 'confirm' | 'prompt';
	readonly show?: boolean;
	readonly input?: string;
	validator?: (value: any) => string | boolean | undefined;
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
	onConfirm?: (payload: {value: string}) => void;
	onDismiss?: () => void;
	onClose?: () => void;
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
		footer,
		show,
		onClose,
		onDismiss,
		id,
		validator,
		onConfirm,
		children,
		choices,
		dangerousHTML: html,
	} = properties;

	const [value, setValue] = useState(input);
	const [valid, setValid] = useState(true);
	const [validatorMessage, setValidatorMessage] = useState('');
	const reference = useRef<HTMLDivElement>(null);
	const [modal, setModal] = useState<BootstrapModal>();

	useEffect(() => {
		if (!reference.current) {
			return;
		}

		const _modal = new BootstrapModal(reference.current);
		setModal(_modal);

		return () => {
			_modal.dispose();
		};
	}, [reference]);

	useEffect(() => {
		if (!show) {
			return;
		}

		const onHidden = () => {
			onClose?.();
		};

		const element = reference.current;
		if (!element) {
			return;
		}

		element.addEventListener('hidden.bs.modal', onHidden);

		return () => {
			element.removeEventListener('hidden.bs.modal', onHidden);
		};
	}, [reference, show, onClose]);

	const handleInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		setValue(event.target.value);
	};

	const confirm = () => {
		if (typeof validator === 'function') {
			const result = validator(value);
			if (typeof result === 'string') {
				setValidatorMessage(result);
				setValid(false);
				return;
			}
		}

		onConfirm?.({value});
		modal?.hide();

		// The "hidden.bs.modal" event can't be trigged automatically when testing.

		if (import.meta.env.NODE_ENV === 'test') {
			$(reference.current!).trigger('hidden.bs.modal');
		}
	};

	const dismiss = () => {
		onDismiss?.();
		modal?.hide();

		if (import.meta.env.NODE_ENV === 'test') {
			$(reference.current!).trigger('hidden.bs.modal');
		}
	};

	useEffect(() => {
		if (show && modal) {
			const timeout = setTimeout(() => {
				modal.show();
			}, 50);
			return () => {
				clearTimeout(timeout);
			};
		}
	}, [show, modal]);

	if (!show) {
		return null;
	}

	return (
		<div ref={reference} id={id} className='modal fade' role='dialog'>
			<div
				className={clsx('modal-dialog', center && 'modal-dialog-centered')}
				role='document'
			>
				<div className={`modal-content bg-${type}`}>
					<ModalHeader show={showHeader} title={title} onDismiss={dismiss}/>
					<ModalBody
						text={text}
						dangerousHTML={html}
						showInput={mode === 'prompt'}
						value={value}
						choices={choices}
						inputType={inputType}
						inputMode={inputMode}
						placeholder={placeholder}
						invalid={!valid}
						validatorMessage={validatorMessage}
						onChange={handleInputChange}
					>
						{children}
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
						{footer}
					</ModalFooter>
				</div>
			</div>
		</div>
	);
};

export default Modal;
