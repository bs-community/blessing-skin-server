import React, { useState } from 'react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { toast } from '@/scripts/notify'
import type { Player } from '@/scripts/types'
import urls from '@/scripts/urls'
import Modal from '@/components/Modal'

type Extra = {
  score: number
  cost: number
  rule: string
  length: string
}

interface Props {
  show: boolean
  onAdd(player: Player): void
  onClose(): void
}

const ModalAddPlayer: React.FC<Props> = (props) => {
  const [name, setName] = useState('')

  const handleNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setName(event.target.value)
  }

  const handleConfirm = async () => {
    const {
      code,
      message,
      data: player,
    } = await fetch.post<fetch.ResponseBody<Player>>(urls.user.player.add(), {
      name,
    })
    if (code === 0) {
      toast.success(message)
      props.onAdd(player)
    } else {
      toast.error(message)
    }
  }

  const handleClose = () => {
    setName('')
    props.onClose()
  }

  const { score, cost, rule, length } = blessing.extra as Extra
  const isScoreEnough = score >= cost

  return (
    <Modal
      show={props.show}
      title={t('user.player.add-player')}
      onConfirm={handleConfirm}
      onClose={handleClose}
    >
      <div className="form-group">
        <label htmlFor="new-player-name">
          {t('general.player.player-name')}
        </label>
        <input
          type="text"
          className="form-control"
          id="new-player-name"
          value={name}
          onChange={handleNameChange}
        />
      </div>
      <div className="callout callout-info">
        <ul className="m-0 p-0 pl-3">
          <li>{rule}</li>
          <li>{length}</li>
        </ul>
      </div>
      <div
        className={`alert alert-${isScoreEnough ? 'success' : 'danger'}`}
        role="alert"
      >
        <i className={`icon fas fa-${isScoreEnough ? 'check' : 'times'}`}></i>
        <span className="ml-1">
          {t('user.cur-score')} {score}
        </span>
      </div>
    </Modal>
  )
}

export default ModalAddPlayer
