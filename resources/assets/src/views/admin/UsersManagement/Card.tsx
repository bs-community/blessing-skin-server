import React from 'react'
import { t } from '@/scripts/i18n'
import { User } from '@/scripts/types'
import { Box, Icon, InfoTable } from './styles'
import {
  humanizePermission,
  verificationStatusText,
  canModifyUser,
  canModifyPermission,
} from './utils'

interface Props {
  user: User
  currentUser: User
  onEmailChange(): void
  onNicknameChange(): void
  onScoreChange(): void
  onPermissionChange(): void
  onVerificationToggle(): void
  onPasswordChange(): void
  onDelete(): void
}

const Card: React.FC<Props> = (props) => {
  const { user, currentUser } = props

  const canModify = canModifyUser(user, currentUser)

  return (
    <Box className="info-box">
      <Icon py>
        <img
          className="bs-avatar"
          src={`${blessing.base_url}/avatar/user/${user.uid}`}
        />
      </Icon>
      <div className="info-box-content">
        <div className="row">
          <div className="col-10">
            <b>{user.nickname}</b>
          </div>
          <div className="col-2">
            {canModify && (
              <div className="float-right dropdown">
                <a
                  className="text-gray"
                  href="#"
                  data-toggle="dropdown"
                  aria-expanded="false"
                >
                  <i className="fas fa-cog"></i>
                </a>
                <div className="dropdown-menu dropdown-menu-right">
                  <a
                    href="#"
                    className="dropdown-item"
                    onClick={props.onEmailChange}
                  >
                    <i className="fas fa-at mr-2"></i>
                    {t('admin.changeEmail')}
                  </a>
                  <a
                    href="#"
                    className="dropdown-item"
                    onClick={props.onNicknameChange}
                  >
                    <i className="fas fa-signature mr-2"></i>
                    {t('admin.changeNickName')}
                  </a>
                  <a
                    href="#"
                    className="dropdown-item"
                    onClick={props.onPasswordChange}
                  >
                    <i className="fas fa-asterisk mr-2"></i>
                    {t('admin.changePassword')}
                  </a>
                  <div className="dropdown-divider"></div>
                  <a
                    href="#"
                    className="dropdown-item"
                    onClick={props.onScoreChange}
                  >
                    <i className="fas fa-coins mr-2"></i>
                    {t('admin.changeScore')}
                  </a>
                  {canModifyPermission(user, currentUser) && (
                    <a
                      href="#"
                      className="dropdown-item"
                      onClick={props.onPermissionChange}
                    >
                      <i className="fas fa-user-secret mr-2"></i>
                      {t('admin.changePermission')}
                    </a>
                  )}
                  <a
                    href="#"
                    className="dropdown-item"
                    onClick={props.onVerificationToggle}
                  >
                    <i className="fas fa-user-check mr-2"></i>
                    {t('admin.toggleVerification')}
                  </a>
                  <div className="dropdown-divider"></div>
                  {canModify && user.uid !== currentUser.uid && (
                    <a
                      href="#"
                      className="dropdown-item dropdown-item-danger"
                      onClick={props.onDelete}
                    >
                      <i className="fas fa-trash mr-2"></i>
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
          <InfoTable className="row m-2 border-top border-bottom">
            <div className="col-sm-4 py-1 text-center">
              <b className="d-block">{t('general.user.score')}</b>
              <span className="d-block py-1">{user.score}</span>
            </div>
            <div className="col-sm-4 py-1 text-center">
              <b className="d-block">{t('admin.permission')}</b>
              <span className="d-block py-1">
                {humanizePermission(user.permission)}
              </span>
            </div>
            <div className="col-sm-4 py-1 text-center">
              <b className="d-block">{t('admin.verification')}</b>
              <span className="d-block py-1">
                {verificationStatusText(user.verified)}
              </span>
            </div>
          </InfoTable>
          <div>
            <small className="text-gray">
              {t('general.user.register-at')}
              {': '}
              {user.register_at}
            </small>
          </div>
        </div>
      </div>
    </Box>
  )
}

export default Card
