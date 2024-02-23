import React, {useState} from 'react';
import {t} from '@/scripts/i18n';
import Modal from '@/components/Modal';

type Properties = {
	readonly show: boolean;
	onSubmit(skin: boolean, cape: boolean): Promise<void>;
	onClose(): void;
};

const ModalReset: React.FC<Properties> = properties => {
	const [skin, setSkin] = useState(false);
	const [cape, setCape] = useState(false);

	const handleSkinChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		setSkin(event.target.checked);
	};

	const handleCapeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		setCape(event.target.checked);
	};

	const handleConfirm = () => {
		properties.onSubmit(skin, cape);
	};

	const handleClose = () => {
		setSkin(false);
		setCape(false);
		properties.onClose();
	};

	return (
		<Modal
			show={properties.show}
			title={t('user.chooseClearTexture')}
			onConfirm={handleConfirm}
			onClose={handleClose}
		>
			<label className='d-block'>
				<input
					type='checkbox'
					className='mr-2'
					checked={skin}
					onChange={handleSkinChange}
				/>
				{t('general.skin')}
			</label>
			<label className='d-block'>
				<input
					type='checkbox'
					className='mr-2'
					checked={cape}
					onChange={handleCapeChange}
				/>
				{t('general.cape')}
			</label>
		</Modal>
	);
};

export default ModalReset;
