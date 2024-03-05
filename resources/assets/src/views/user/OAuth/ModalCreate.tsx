import {useState} from 'react';
import {t} from '@/scripts/i18n';
import Modal from '@/components/Modal';

type Properties = {
	readonly show: boolean;
	onCreate(name: string, redirect: string): Promise<void>;
	onClose(): void;
};

const ModalCreate: React.FC<Properties> = properties => {
	const [name, setName] = useState('');
	const [url, setUrl] = useState('');

	const handleNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		setName(event.target.value);
	};

	const handleUrlChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		setUrl(event.target.value);
	};

	const handleComplete = () => {
		properties.onCreate(name, url);
	};

	const handleDismiss = () => {
		setName('');
		setUrl('');
	};

	return (
		<Modal
			show={properties.show}
			onConfirm={handleComplete}
			onDismiss={handleDismiss}
			onClose={properties.onClose}
		>
			<div className='form-group'>
				<label htmlFor='new-app-name'>{t('user.oauth.name')}</label>
				<input
					required
					value={name}
					className='form-control'
					id='new-app-name'
					type='text'
					onChange={handleNameChange}
				/>
			</div>
			<div className='form-group'>
				<label htmlFor='new-app-redirect'>{t('user.oauth.redirect')}</label>
				<input
					required
					value={url}
					className='form-control'
					id='new-app-redirect'
					type='url'
					onChange={handleUrlChange}
				/>
			</div>
		</Modal>
	);
};

export default ModalCreate;
