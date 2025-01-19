import {createRoot} from 'react-dom/client';
import Modal, {type ModalOptions, type ModalResult} from '../components/Modal';

export async function showModal(options: ModalOptions = {}): Promise<ModalResult> {
	return new Promise((resolve, reject) => {
		const container = document.createElement('div');
		document.body.append(container);
		const root = createRoot(container);

		const handleClose = () => {
			root.unmount();
			container.remove();
		};

		root.render((
			<Modal
				{...options}
				show
				center
				onConfirm={resolve}
				onDismiss={reject}
				onClose={handleClose}
			/>
		));
	});
}
