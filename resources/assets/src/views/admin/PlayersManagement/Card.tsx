import React from 'react'
import { t } from '@/scripts/i18n'
import { showModal } from '@/scripts/notify'
import type { Player } from '@/scripts/types'
import { Box } from './styles'

interface Props {
  player: Player
  onUpdateName(): void
  onUpdateOwner(): void
  onUpdateTexture(): void
  onDelete(): void
}

const Card: React.FC<Props> = (props) => {
  const { player } = props

  const handlePreviewTextures = () => {
    showModal({
      mode: 'alert',
      title: t('general.player.previews'),
      children: (
        <div className="row">
          <div className="col-6 d-flex justify-content-center">
            {player.tid_skin > 0 && (
              <a
                href={`${blessing.base_url}/skinlib/show/${player.tid_skin}`}
                target="_blank"
              >
                <img
                  src={`${blessing.base_url}/preview/${player.tid_skin}`}
                  alt={`${player.name} - ${t('general.skin')}`}
                  width="128"
                />
              </a>
            )}
          </div>
          <div className="col-6 d-flex justify-content-center">
            {player.tid_cape > 0 && (
              <a
                href={`${blessing.base_url}/skinlib/show/${player.tid_cape}`}
                target="_blank"
              >
                <img
                  src={`${blessing.base_url}/preview/${player.tid_cape}`}
                  alt={`${player.name} - ${t('general.cape')}`}
                  width="128"
                />
              </a>
            )}
          </div>
        </div>
      ),
    })
  }

  return (
    <Box className="info-box">
      <div className="info-box-icon">
        <img
          className="bs-avatar"
          src={`${blessing.base_url}/avatar/player/${player.name}`}
        />
      </div>
      <div className="info-box-content">
        <div className="row">
          <div className="col-10">
            <b>{player.name}</b>
          </div>
          <div className="col-2">
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
                  onClick={handlePreviewTextures}
                >
                  <i className="fas fa-eye mr-2"></i>
                  {t('general.player.previews')}
                </a>
                <div className="dropdown-divider"></div>
                <a
                  href="#"
                  className="dropdown-item"
                  onClick={props.onUpdateName}
                >
                  <i className="fas fa-signature mr-2"></i>
                  {t('admin.changePlayerName')}
                </a>
                <a
                  href="#"
                  className="dropdown-item"
                  onClick={props.onUpdateOwner}
                >
                  <i className="fas fa-user-edit mr-2"></i>
                  {t('admin.changeOwner')}
                </a>
                <a
                  href="#"
                  className="dropdown-item"
                  onClick={props.onUpdateTexture}
                >
                  <i className="fas fa-tshirt mr-2"></i>
                  {t('admin.changeTexture')}
                </a>
                <div className="dropdown-divider"></div>
                <a
                  href="#"
                  className="dropdown-item dropdown-item-danger"
                  onClick={props.onDelete}
                >
                  <i className="fas fa-trash mr-2"></i>
                  {t('admin.deletePlayer')}
                </a>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div>
            <span className="mr-2">PID: {player.pid}</span>
            <span>
              {t('general.player.owner')}: {player.uid}
            </span>
          </div>
          <div>
            <small className="text-gray">
              {`${t('general.player.last-modified')}: `}
              {player.last_modified}
            </small>
          </div>
        </div>
      </div>
    </Box>
  )
}

export default Card
