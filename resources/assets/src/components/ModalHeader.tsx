
export type Props = {
	readonly title?: string;
};

type InternalProperties = {
	onDismiss?: () => void;
	readonly show?: boolean;
};

const ModalHeader: React.FC<Props & InternalProperties> = ({show, title, onDismiss}) =>
	show
		? (
			<div className='modal-header'>
				<h5 className='modal-title'>{title}</h5>
				<button
					type='button'
					className='btn-close'
					data-bs-dismiss='modal'
					aria-label='Close'
					onClick={onDismiss}
				/>
			</div>
		)
		: null;

export default ModalHeader;
