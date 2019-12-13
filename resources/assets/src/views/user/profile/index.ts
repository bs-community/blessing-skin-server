import resetAvatar from './resetAvatar'
import passwordFormHandler from './password'
import nicknameFormHandler from './nickname'
import emailFormHandler from './email'
import deleteAccountFormHandler from './deleteAccount'

const btnResetAvatar = document.querySelector('#reset-avatar')
if (btnResetAvatar) {
  btnResetAvatar.addEventListener('click', resetAvatar)
}

const passwordForm = document.querySelector<HTMLFormElement>('#change-password')
if (passwordForm) {
  passwordForm.addEventListener('submit', passwordFormHandler)
}

const nicknameForm = document.querySelector<HTMLFormElement>('#change-nickname')
if (nicknameForm) {
  nicknameForm.addEventListener('submit', nicknameFormHandler)
}

const emailForm = document.querySelector<HTMLFormElement>('#change-email')
if (emailForm) {
  emailForm.addEventListener('submit', emailFormHandler)
}

const deleteAccountForm = document
  .querySelector<HTMLFormElement>('#modal-delete-account')
if (deleteAccountForm) {
  deleteAccountForm.addEventListener('submit', deleteAccountFormHandler)
}
