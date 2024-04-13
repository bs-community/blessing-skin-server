import ReactDOM from 'react-dom';
import Modal, {type ModalOptions, type ModalResult} from '../components/Modal';

export async function showModal(options: ModalOptions = {}): Promise<ModalResult> {
	return new Promise((resolve, reject) => {
		const container = document.createElement('div');
		document.body.append(container);

		const handleClose = () => {
			ReactDOM.unmountComponentAtNode(container);
			container.remove();
		};

		ReactDOM.render(
			<Modal
				{...options}
				show
				center
				onConfirm={resolve}
				onDismiss={reject}
				onClose={handleClose}
			/>,
			container,
		);
	});
}
