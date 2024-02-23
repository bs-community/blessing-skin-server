import React from 'react';

export type Props = {
	readonly flexFooter?: boolean;
	readonly okButtonText?: string;
	readonly okButtonType?: string;
	readonly cancelButtonText?: string;
	readonly cancelButtonType?: string;
	readonly children?: React.ReactNode;
};

type InternalProperties = {
	readonly showCancelButton: boolean;
	onConfirm?(): void;
	onDismiss?(): void;
};

const ModalFooter: React.FC<InternalProperties & Props> = properties => {
	const classes = ['modal-footer'];
	if (properties.flexFooter) {
		classes.push('d-flex', 'justify-content-between');
	}

	const footerClass = classes.join(' ');

	return properties.children ? (
		<div className={footerClass}>{properties.children}</div>
	) : (
		<div className={footerClass}>
			{properties.showCancelButton && (
				<button
					type='button'
					className={`btn btn-${properties.cancelButtonType}`}
					data-dismiss='modal'
					onClick={properties.onDismiss}
				>
					{properties.cancelButtonText}
				</button>
			)}
			<button
				type='button'
				className={`btn btn-${properties.okButtonType}`}
				onClick={properties.onConfirm}
			>
				{properties.okButtonText}
			</button>
		</div>
	);
};

export default ModalFooter;
