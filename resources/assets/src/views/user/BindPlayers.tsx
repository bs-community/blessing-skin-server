import React, { useState, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal } from '@/scripts/notify'
import { Player } from '@/scripts/types'
import Loading from '@/components/Loading'

const BindPlayers: React.FC = () => {
  const [players, setPlayers] = useState<string[]>([])
  const [selected, setSelected] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const [isPending, setIsPending] = useState(false)

  useEffect(() => {
    const getPlayers = async () => {
      setIsLoading(true)
      const response = await fetch.get<fetch.ResponseBody<Player[]>>(
        '/user/player/list',
      )
      const players = response.data.map((player) => player.name)
      setPlayers(players)
      setSelected(players[0])
      setIsLoading(false)
    }
    getPlayers()
  }, [])

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setIsPending(true)

    const { code, message } = await fetch.post<fetch.ResponseBody>(
      '/user/player/bind',
      { player: selected },
    )
    if (code === 0) {
      await showModal({ mode: 'alert', text: message })
      window.location.href = `${blessing.base_url}/user`
    } else {
      showModal({ mode: 'alert', text: message })
    }

    setIsPending(false)
  }

  return isLoading ? (
    <Loading />
  ) : (
    <form method="post" onSubmit={handleSubmit}>
      {players.length > 0 ? (
        <>
          <p>{t('user.bindExistedPlayer')}</p>
          <div className="mb-3">
            {players.map((player) => (
              <label className="d-block mb-1">
                <input
                  key={player}
                  type="radio"
                  className="mr-2"
                  checked={selected === player}
                  onChange={() => setSelected(player)}
                />
                {player}
              </label>
            ))}
          </div>
        </>
      ) : (
        <>
          <p>{t('user.bindNewPlayer')}</p>
          <input
            type="text"
            className="form-control mb-3"
            placeholder={t('general.player.player-name')}
            onChange={(e) => setSelected(e.target.value)}
          />
        </>
      )}
      <button
        className="btn btn-primary float-right"
        type="submit"
        disabled={isPending}
      >
        {isPending ? (
          <>
            <i className="fas fa-spinner fa-spin mr-1"></i>
            {t('general.wait')}
          </>
        ) : (
          t('general.submit')
        )}
      </button>
    </form>
  )
}

export default hot(BindPlayers)
