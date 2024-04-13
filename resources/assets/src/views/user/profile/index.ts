import resetAvatar from './resetAvatar';
import passwordFormHandler from './password';
import nicknameFormHandler from './nickname';
import emailFormHandler from './email';
import deleteAccountFormHandler from './deleteAccount';

const buttonResetAvatar = document.querySelector('#reset-avatar');
buttonResetAvatar?.addEventListener('click', resetAvatar);

const passwordForm = document.querySelector<HTMLFormElement>('#change-password');
passwordForm?.addEventListener('submit', passwordFormHandler);

const nicknameForm = document.querySelector<HTMLFormElement>('#change-nickname');
nicknameForm?.addEventListener('submit', nicknameFormHandler);

const emailForm = document.querySelector<HTMLFormElement>('#change-email');
emailForm?.addEventListener('submit', emailFormHandler);

const deleteAccountForm = document.querySelector<HTMLFormElement>(
	'#modal-delete-account',
);
deleteAccountForm?.addEventListener('submit', deleteAccountFormHandler);
