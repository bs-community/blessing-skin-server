
import Button from './Button';
import type {Filter} from './types';
import {humanizeType} from './utils';
import {TextureType} from '@/scripts/types';
import {t} from '@/scripts/i18n';

type Properties = {
	readonly filter: Filter;
	onChange(filter: Filter): void;
};

const FilterSelector: React.FC<Properties> = properties => {
	const {filter, onChange} = properties;

	const handleSkinClick = () => {
		onChange('skin');
	};

	const handleSteveClick = () => {
		onChange(TextureType.Steve);
	};

	const handleAlexClick = () => {
		onChange(TextureType.Alex);
	};

	const handleCapeClick = () => {
		onChange(TextureType.Cape);
	};

	return (
		<>
			<button
				className='btn btn-default dropdown-toggle'
				type='button'
				data-toggle='dropdown'
			>
				{humanizeType(filter)}
			</button>
			<div className='dropdown-menu'>
				<Button
					className='dropdown-item'
					active={filter === 'skin'}
					onClick={handleSkinClick}
				>
					{t('general.skin')}
				</Button>
				<Button
					className='dropdown-item'
					active={filter === TextureType.Steve}
					onClick={handleSteveClick}
				>
					Steve
				</Button>
				<Button
					className='dropdown-item'
					active={filter === TextureType.Alex}
					onClick={handleAlexClick}
				>
					Alex
				</Button>
				<Button
					className='dropdown-item'
					active={filter === TextureType.Cape}
					onClick={handleCapeClick}
				>
					{t('general.cape')}
				</Button>
			</div>
		</>
	);
};

export default FilterSelector;
