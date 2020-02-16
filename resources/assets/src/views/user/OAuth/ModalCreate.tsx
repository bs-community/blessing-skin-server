import React, { useState } from 'react'
import { t } from '../../../scripts/i18n'
import Modal from '../../../components/Modal'

interface Props {
  show: boolean
  onCreate(name: string, redirect: string): Promise<void>
  onClose(): void
}

const ModalCreate: React.FC<Props> = props => {
  const [name, setName] = useState('')
  const [url, setUrl] = useState('')

  const handleNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setName(event.target.value)
  }

  const handleUrlChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setUrl(event.target.value)
  }

  const handleComplete = () => {
    props.onCreate(name, url)
  }

  const handleDismiss = () => {
    setName('')
    setUrl('')
  }

  return (
    <Modal
      show={props.show}
      onConfirm={handleComplete}
      onDismiss={handleDismiss}
      onClose={props.onClose}
    >
      <div className="form-group">
        <label htmlFor="new-app-name">{t('user.oauth.name')}</label>
        <input
          value={name}
          onChange={handleNameChange}
          className="form-control"
          id="new-app-name"
          type="text"
          required
        />
      </div>
      <div className="form-group">
        <label htmlFor="new-app-redirect">{t('user.oauth.redirect')}</label>
        <input
          value={url}
          onChange={handleUrlChange}
          className="form-control"
          id="new-app-redirect"
          type="url"
          required
        />
      </div>
    </Modal>
  )
}

export default ModalCreate
