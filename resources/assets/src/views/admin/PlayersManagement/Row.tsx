import React from 'react'
import { t } from '@/scripts/i18n'
import type { Player } from '@/scripts/types'
import ButtonEdit from '@/components/ButtonEdit'

interface Props {
  player: Player
  onUpdateName(): void
  onUpdateOwner(): void
  onUpdateTexture(): void
  onDelete(): void
}

const Row: React.FC<Props> = (props) => {
  const { player } = props

  return (
    <tr>
      <td>{player.pid}</td>
      <td>
        {player.name}
        <span className="ml-1">
          <ButtonEdit
            title={t('admin.changePlayerName')}
            onClick={props.onUpdateName}
          />
        </span>
      </td>
      <td>
        {player.uid}
        <span className="ml-1">
          <ButtonEdit
            title={t('admin.changeOwner')}
            onClick={props.onUpdateOwner}
          />
        </span>
      </td>
      <td>
        {player.tid_skin > 0 && (
          <a
            href={`${blessing.base_url}/skinlib/show/${player.tid_skin}`}
            target="_blank"
            className="mr-1"
          >
            <img
              src={`${blessing.base_url}/preview/${player.tid_skin}`}
              alt={`${player.name} - ${t('general.skin')}`}
              width="64"
            />
          </a>
        )}
        {player.tid_cape > 0 && (
          <a
            href={`${blessing.base_url}/skinlib/show/${player.tid_cape}`}
            target="_blank"
          >
            <img
              src={`${blessing.base_url}/preview/${player.tid_cape}`}
              alt={`${player.name} - ${t('general.cape')}`}
              width="64"
            />
          </a>
        )}
      </td>
      <td>{player.last_modified}</td>
      <td className="d-flex flex-wrap">
        <button
          className="btn btn-default mr-2"
          onClick={props.onUpdateTexture}
        >
          {t('admin.changeTexture')}
        </button>
        <button className="btn btn-danger" onClick={props.onDelete}>
          {t('admin.deletePlayer')}
        </button>
      </td>
    </tr>
  )
}

export default Row
