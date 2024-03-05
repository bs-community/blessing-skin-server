
export type Props = {
	readonly title?: string;
};

type InternalProperties = {
	onDismiss?(): void;
	readonly show?: boolean;
};

const ModalHeader: React.FC<Props & InternalProperties> = properties =>
	properties.show ? (
		<div className='modal-header'>
			<h5 className='modal-title'>{properties.title}</h5>
			<button
				type='button'
				className='close'
				data-dismiss='modal'
				aria-label='Close'
				onClick={properties.onDismiss}
			>
				<span aria-hidden>&times;</span>
			</button>
		</div>
	) : null;

export default ModalHeader;
