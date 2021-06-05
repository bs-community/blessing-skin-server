import React from 'react'
import { t } from '@/scripts/i18n'
import ButtonEdit from '@/components/ButtonEdit'
import type { App } from './types'

interface Props {
  app: App
  onEditName: React.MouseEventHandler<HTMLAnchorElement>
  onEditRedirect: React.MouseEventHandler<HTMLAnchorElement>
  onDelete: React.MouseEventHandler<HTMLButtonElement>
}

const Row: React.FC<Props> = (props) => {
  const { app } = props

  return (
    <tr>
      <td>{app.id}</td>
      <td>
        <span>{app.name}</span>
        <ButtonEdit
          title={t('user.oauth.modifyName')}
          onClick={props.onEditName}
        />
      </td>
      <td>{app.secret}</td>
      <td>
        <span>{app.redirect}</span>
        <ButtonEdit
          title={t('user.oauth.modifyUrl')}
          onClick={props.onEditRedirect}
        />
      </td>
      <td>
        <button className="btn btn-danger" onClick={props.onDelete}>
          {t('report.delete')}
        </button>
      </td>
    </tr>
  )
}

export default Row
