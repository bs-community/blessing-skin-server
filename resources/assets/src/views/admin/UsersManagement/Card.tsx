import type React from 'react';
import clsx from 'clsx';
import {Box, Icon, InfoTable} from './styles';
import {
	humanizePermission,
	verificationStatusText,
	canModifyUser,
	canModifyPermission,
} from './utils';
import {t} from '@/scripts/i18n';
import type {User} from '@/scripts/types';

type Properties = {
	readonly user: User;
	readonly currentUser: User;
	onEmailChange(): void;
	onNicknameChange(): void;
	onScoreChange(): void;
	onPermissionChange(): void;
	onVerificationToggle(): void;
	onPasswordChange(): void;
	onDelete(): void;
};

const Card: React.FC<Properties> = properties => {
	const {user, currentUser} = properties;

	const isDarkMode = document.body.classList.contains('dark-mode');

	const avatar = `${blessing.base_url}/avatar/user/${user.uid}`;
	const avatarPNG = `${avatar}?png`;
	const canModify = canModifyUser(user, currentUser);

	return (
		<Box className={clsx('info-box', {'bg-gray-dark': isDarkMode})}>
			<Icon py>
				<picture>
					<source srcSet={avatar} type='image/webp'/>
					<img className='bs-avatar' src={avatarPNG}/>
				</picture>
			</Icon>
			<div className='info-box-content'>
				<div className='row'>
					<div className='col-10'>
						<b>{user.nickname}</b>
					</div>
					<div className='col-2'>
						{canModify && (
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
										onClick={properties.onEmailChange}
									>
										<i className='fas fa-at mr-2'/>
										{t('admin.changeEmail')}
									</a>
									<a
										href='#'
										className='dropdown-item'
										onClick={properties.onNicknameChange}
									>
										<i className='fas fa-signature mr-2'/>
										{t('admin.changeNickName')}
									</a>
									<a
										href='#'
										className='dropdown-item'
										onClick={properties.onPasswordChange}
									>
										<i className='fas fa-asterisk mr-2'/>
										{t('admin.changePassword')}
									</a>
									<div className='dropdown-divider'/>
									<a
										href='#'
										className='dropdown-item'
										onClick={properties.onScoreChange}
									>
										<i className='fas fa-coins mr-2'/>
										{t('admin.changeScore')}
									</a>
									{canModifyPermission(user, currentUser) && (
										<a
											href='#'
											className='dropdown-item'
											onClick={properties.onPermissionChange}
										>
											<i className='fas fa-user-secret mr-2'/>
											{t('admin.changePermission')}
										</a>
									)}
									<a
										href='#'
										className='dropdown-item'
										onClick={properties.onVerificationToggle}
									>
										<i className='fas fa-user-check mr-2'/>
										{t('admin.toggleVerification')}
									</a>
									<div className='dropdown-divider'/>
									{canModify && user.uid !== currentUser.uid && (
										<a
											href='#'
											className='dropdown-item dropdown-item-danger'
											onClick={properties.onDelete}
										>
											<i className='fas fa-trash mr-2'/>
											{t('admin.deleteUser')}
										</a>
									)}
								</div>
							</div>
						)}
					</div>
				</div>
				<div>
					<div>UID: {user.uid}</div>
					<div>
						{t('general.user.email')}
						{': '}
						<span>{user.email}</span>
					</div>
					<InfoTable className='row m-2 border-top border-bottom'>
						<div className='col-sm-4 py-1 text-center'>
							<b className='d-block'>{t('general.user.score')}</b>
							<span className='d-block py-1'>{user.score}</span>
						</div>
						<div className='col-sm-4 py-1 text-center'>
							<b className='d-block'>{t('admin.permission')}</b>
							<span className='d-block py-1'>
								{humanizePermission(user.permission)}
							</span>
						</div>
						<div className='col-sm-4 py-1 text-center'>
							<b className='d-block'>{t('admin.verification')}</b>
							<span className='d-block py-1'>
								{verificationStatusText(user.verified)}
							</span>
						</div>
					</InfoTable>
					<div>
						<small className='text-gray'>
							{t('general.user.register-at')}
							{': '}
							{user.register_at}
						</small>
					</div>
				</div>
			</div>
		</Box>
	);
};

export default Card;
