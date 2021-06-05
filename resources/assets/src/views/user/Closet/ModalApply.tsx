import React, { useState, useEffect } from 'react'
import $ from 'jquery'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { toast } from '@/scripts/notify'
import type { Player } from '@/scripts/types'
import urls from '@/scripts/urls'
import Loading from '@/components/Loading'
import Modal from '@/components/Modal'

const baseUrl = blessing.base_url

interface Props {
  show: boolean
  canAdd: boolean
  skin?: number
  cape?: number
  onClose(): void
}

const ModalApply: React.FC<Props> = (props) => {
  const [players, setPlayers] = useState<Player[]>([])
  const [search, setSearch] = useState('')
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    if (!props.show) {
      return
    }

    const getPlayers = async () => {
      setIsLoading(true)
      const players = await fetch.get<Player[]>(urls.user.player.list())
      setPlayers(players)
      setIsLoading(false)
    }
    getPlayers()
  }, [props.show])

  const handleSearch = (event: React.ChangeEvent<HTMLInputElement>) => {
    setSearch(event.target.value)
  }

  const handleSelect = async (player: Player) => {
    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.user.player.set(player.pid),
      {
        skin: props.skin,
        cape: props.cape,
      },
    )
    if (code === 0) {
      toast.success(message)
      $('#modal-apply').modal('hide')
    } else {
      toast.error(message)
    }
  }

  return (
    <Modal
      show={props.show}
      id="modal-apply"
      title={t('user.closet.use-as.title')}
      flexFooter
      footer={<></>}
      onClose={props.onClose}
    >
      {isLoading ? (
        <Loading />
      ) : players.length === 0 ? (
        <p>{t('user.closet.use-as.empty')}</p>
      ) : (
        <>
          <div className="form-group">
            <input
              type="text"
              className="form-control"
              placeholder={t('user.typeToSearch')}
              onChange={handleSearch}
            />
          </div>
          {players
            .filter((player) => player.name.includes(search))
            .map((player) => (
              <button
                key={player.pid}
                className="btn btn-block btn-outline-info text-left"
                title={player.name}
                onClick={() => handleSelect(player)}
              >
                <img
                  src={`${baseUrl}/avatar/${player.tid_skin}?3d&size=45`}
                  alt={player.name}
                  width={45}
                  height={45}
                />
                <span className="ml-1">{player.name}</span>
              </button>
            ))}
        </>
      )}
    </Modal>
  )
}

export default ModalApply
