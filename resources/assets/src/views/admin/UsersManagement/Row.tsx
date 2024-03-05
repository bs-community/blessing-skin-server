
import {
	humanizePermission,
	verificationStatusText,
	canModifyUser,
	canModifyPermission,
} from './utils';
import {t} from '@/scripts/i18n';
import type {User} from '@/scripts/types';
import ButtonEdit from '@/components/ButtonEdit';

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

const Row: React.FC<Properties> = properties => {
	const {user, currentUser} = properties;

	const canModify = canModifyUser(user, currentUser);

	return (
		<tr>
			<td>{user.uid}</td>
			<td>
				{user.email}
				{canModify && (
					<span className='ml-1'>
						<ButtonEdit
							title={t('admin.changeEmail')}
							onClick={properties.onEmailChange}
						/>
					</span>
				)}
			</td>
			<td>
				{user.nickname}
				{canModify && (
					<span className='ml-1'>
						<ButtonEdit
							title={t('admin.changeNickName')}
							onClick={properties.onNicknameChange}
						/>
					</span>
				)}
			</td>
			<td>
				{user.score}
				{canModify && (
					<span className='ml-1'>
						<ButtonEdit
							title={t('admin.changeScore')}
							onClick={properties.onScoreChange}
						/>
					</span>
				)}
			</td>
			<td>
				{humanizePermission(user.permission)}
				{canModifyPermission(user, currentUser) && (
					<span className='ml-1'>
						<ButtonEdit
							title={t('admin.changePermission')}
							onClick={properties.onPermissionChange}
						/>
					</span>
				)}
			</td>
			<td>
				{verificationStatusText(user.verified)}
				{canModify && (
					<a
						className='ml-1'
						href='#'
						title={t('admin.toggleVerification')}
						onClick={properties.onVerificationToggle}
					>
						{user.verified ? (
							<i className='fas fa-toggle-on'/>
						) : (
							<i className='fas fa-toggle-off'/>
						)}
					</a>
				)}
			</td>
			<td>{user.register_at}</td>
			<td>
				<button
					className='btn btn-default mr-2'
					disabled={!canModify}
					onClick={properties.onPasswordChange}
				>
					{t('admin.changePassword')}
				</button>
				<button
					className='btn btn-danger'
					disabled={!canModify || user.uid === currentUser.uid}
					onClick={properties.onDelete}
				>
					{t('admin.deleteUser')}
				</button>
			</td>
		</tr>
	);
};

export default Row;
