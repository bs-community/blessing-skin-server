import React, { useState } from 'react'
import Modal from '../../../components/Modal'
import { trans } from '../../../scripts/i18n'

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
      <table className="table">
        <tbody>
          <tr>
            <td className="key">{trans('user.oauth.name')}</td>
            <td className="value">
              <input
                value={name}
                onChange={handleNameChange}
                className="form-control"
                placeholder={trans('user.oauth.name')}
                type="text"
                required
              />
            </td>
          </tr>
          <tr>
            <td className="key">{trans('user.oauth.redirect')}</td>
            <td className="value">
              <input
                value={url}
                onChange={handleUrlChange}
                className="form-control"
                placeholder={trans('user.oauth.redirect')}
                type="url"
                required
              />
            </td>
          </tr>
        </tbody>
      </table>
    </Modal>
  )
}

export default ModalCreate
