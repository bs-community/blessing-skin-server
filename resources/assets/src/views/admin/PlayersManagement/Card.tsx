
import clsx from 'clsx';
import {Box} from './styles';
import {t} from '@/scripts/i18n';
import {showModal} from '@/scripts/notify';
import type {Player} from '@/scripts/types';

type Properties = {
	readonly player: Player;
	onUpdateName(): void;
	onUpdateOwner(): void;
	onUpdateTexture(): void;
	onDelete(): void;
};

const Card: React.FC<Properties> = properties => {
	const {player} = properties;

	const handlePreviewTextures = () => {
		const skinPreview = `${blessing.base_url}/preview/${player.tid_skin}`;
		const skinPreviewPNG = `${skinPreview}?png`;
		const capePreview = `${blessing.base_url}/preview/${player.tid_cape}`;
		const capePreviewPNG = `${capePreview}?png`;

		showModal({
			mode: 'alert',
			title: t('general.player.previews'),
			children: (
				<div className='row'>
					<div className='col-6 d-flex justify-content-center'>
						{player.tid_skin > 0 && (
							<a
								href={`${blessing.base_url}/skinlib/show/${player.tid_skin}`}
								target='_blank' rel='noreferrer'
							>
								<picture>
									<source srcSet={skinPreview} type='image/webp'/>
									<img
										src={skinPreviewPNG}
										alt={`${player.name} - ${t('general.skin')}`}
										width='128'
									/>
								</picture>
							</a>
						)}
					</div>
					<div className='col-6 d-flex justify-content-center'>
						{player.tid_cape > 0 && (
							<a
								href={`${blessing.base_url}/skinlib/show/${player.tid_cape}`}
								target='_blank' rel='noreferrer'
							>
								<picture>
									<source srcSet={capePreview} type='image/webp'/>
									<img
										src={capePreviewPNG}
										alt={`${player.name} - ${t('general.cape')}`}
										width='128'
									/>
								</picture>
							</a>
						)}
					</div>
				</div>
			),
		});
	};

	const isDarkMode = document.body.classList.contains('dark-mode');

	const avatar = `${blessing.base_url}/avatar/player/${player.name}`;
	const avatarPNG = `${avatar}?png`;

	return (
		<Box className={clsx('info-box', {'bg-gray-dark': isDarkMode})}>
			<div className='info-box-icon'>
				<picture>
					<source srcSet={avatar} type='image/webp'/>
					<img className='bs-avatar' src={avatarPNG}/>
				</picture>
			</div>
			<div className='info-box-content'>
				<div className='row'>
					<div className='col-10'>
						<b>{player.name}</b>
					</div>
					<div className='col-2'>
						<div className='float-right dropdown'>
							<a
								className='text-gray'
								href='#'
								data-toggle='dropdown'
								aria-expanded='false'
							>
								<i className='fas fa-cog'/>
							</a>
							<div className='dropdown-menu dropdown-menu-right'>
								<a
									href='#'
									className='dropdown-item'
									onClick={handlePreviewTextures}
								>
									<i className='fas fa-eye mr-2'/>
									{t('general.player.previews')}
								</a>
								<div className='dropdown-divider'/>
								<a
									href='#'
									className='dropdown-item'
									onClick={properties.onUpdateName}
								>
									<i className='fas fa-signature mr-2'/>
									{t('admin.changePlayerName')}
								</a>
								<a
									href='#'
									className='dropdown-item'
									onClick={properties.onUpdateOwner}
								>
									<i className='fas fa-user-edit mr-2'/>
									{t('admin.changeOwner')}
								</a>
								<a
									href='#'
									className='dropdown-item'
									onClick={properties.onUpdateTexture}
								>
									<i className='fas fa-tshirt mr-2'/>
									{t('admin.changeTexture')}
								</a>
								<div className='dropdown-divider'/>
								<a
									href='#'
									className='dropdown-item dropdown-item-danger'
									onClick={properties.onDelete}
								>
									<i className='fas fa-trash mr-2'/>
									{t('admin.deletePlayer')}
								</a>
							</div>
						</div>
					</div>
				</div>
				<div>
					<div>
						<span className='mr-2'>PID: {player.pid}</span>
						<span>
							{t('general.player.owner')}: {player.uid}
						</span>
					</div>
					<div>
						<small className='text-gray'>
							{`${t('general.player.last-modified')}: `}
							{player.last_modified}
						</small>
					</div>
				</div>
			</div>
		</Box>
	);
};

export default Card;
