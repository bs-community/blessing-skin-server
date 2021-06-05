import React, { useState, useEffect, useLayoutEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { useImmer } from 'use-immer'
import useIsLargeScreen from '@/scripts/hooks/useIsLargeScreen'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import type { Player, Paginator } from '@/scripts/types'
import { toast, showModal } from '@/scripts/notify'
import urls from '@/scripts/urls'
import Pagination from '@/components/Pagination'
import Header from '../UsersManagement/Header'
import Card from './Card'
import LoadingCard from './LoadingCard'
import Row from './Row'
import LoadingRow from './LoadingRow'
import ModalUpdateTexture from './ModalUpdateTexture'

const PlayersManagement: React.FC = () => {
  const [players, setPlayers] = useImmer<Player[]>([])
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [isLoading, setIsLoading] = useState(false)
  const isLargeScreen = useIsLargeScreen()
  const [isTableMode, setIsTableMode] = useState(false)
  const [query, setQuery] = useState('')
  const [textureUpdating, setTextureUpdating] = useState(-1)

  useLayoutEffect(() => {
    if (isLargeScreen) {
      setIsTableMode(true)
    }
  }, [isLargeScreen])

  const getPlayers = async () => {
    setIsLoading(true)
    const { data, last_page }: Paginator<Player> = await fetch.get(
      urls.admin.players.list(),
      {
        q: query,
        page,
      },
    )
    setTotalPages(last_page)
    setPlayers(() => data)
    setIsLoading(false)
  }

  useEffect(() => {
    getPlayers()
  }, [page])

  const handleModeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setIsTableMode(event.target.value === 'table')
  }

  const handleQueryChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setQuery(event.target.value)
  }

  const handleSubmitQuery = (event: React.FormEvent) => {
    event.preventDefault()
    getPlayers()
  }

  const handleUpdateName = async (player: Player, index: number) => {
    let name: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.changePlayerNameNotice'),
        input: player.name,
        validator: (value: string) => {
          if (!value) {
            return t('admin.emptyPlayerName')
          }
        },
      })
      name = value
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.players.name(player.pid),
      { player_name: name },
    )
    if (code === 0) {
      toast.success(message)
      setPlayers((players) => {
        players[index]!.name = name
      })
    } else {
      toast.error(message)
    }
  }

  const handleUpdateOwner = async (player: Player, index: number) => {
    let uid: number
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.changePlayerOwner'),
        input: player.uid.toString(),
        inputMode: 'numeric',
      })
      uid = Number.parseInt(value)
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.players.owner(player.pid),
      { uid },
    )
    if (code === 0) {
      toast.success(message)
      setPlayers((players) => {
        players[index]!.uid = uid
      })
    } else {
      toast.error(message)
    }
  }

  const handleCloseModalUpdateTexture = () => setTextureUpdating(-1)

  const handleUpdateTexture = async (type: 'skin' | 'cape', tid: number) => {
    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.players.texture(players[textureUpdating]!.pid),
      { type, tid },
    )

    if (code === 0) {
      toast.success(message)
      setPlayers((players) => {
        const field = `tid_${type}` as const
        players[textureUpdating]![field] = tid
      })
    } else {
      toast.error(message)
    }
  }

  const handleDelete = async (player: Player) => {
    try {
      await showModal({
        text: t('admin.deletePlayerNotice'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    const { code, message } = await fetch.del<fetch.ResponseBody>(
      urls.admin.players.delete(player.pid),
    )
    if (code === 0) {
      setPlayers((players) => players.filter(({ pid }) => pid !== player.pid))
      toast.success(message)
    } else {
      toast.error(message)
    }
  }

  return (
    <div className="card">
      <Header className="card-header">
        <form className="input-group" onSubmit={handleSubmitQuery}>
          <input
            type="text"
            inputMode="search"
            className="form-control"
            title={t('vendor.datatable.search')}
            value={query}
            onChange={handleQueryChange}
          />
          <div className="input-group-append">
            <button className="btn btn-primary" type="submit">
              {t('vendor.datatable.search')}
            </button>
          </div>
        </form>
        <div className="btn-group btn-group-toggle">
          <label
            className={`btn btn-secondary ${isTableMode ? 'active' : ''}`}
            title="Table Mode"
          >
            <input
              type="radio"
              value="table"
              checked={isTableMode}
              onChange={handleModeChange}
            />
            <i className="fas fa-list"></i>
          </label>
          <label
            className={`btn btn-secondary ${isTableMode ? '' : 'active'}`}
            title="Card Mode"
          >
            <input
              type="radio"
              value="card"
              checked={!isTableMode}
              onChange={handleModeChange}
            />
            <i className="fas fa-grip-vertical"></i>
          </label>
        </div>
      </Header>
      {players.length === 0 && !isLoading ? (
        <div className="card-body text-center">{t('general.noResult')}</div>
      ) : isTableMode ? (
        <div className="card-body table-responsive p-0">
          <table className={`table ${isLoading ? '' : 'table-striped'}`}>
            <thead>
              <tr>
                <th>PID</th>
                <th>{t('general.player.player-name')}</th>
                <th>{t('general.player.owner')}</th>
                <th>{t('general.player.previews')}</th>
                <th>{t('general.player.last-modified')}</th>
                <th>{t('admin.operationsTitle')}</th>
              </tr>
            </thead>
            <tbody>
              {isLoading
                ? new Array(10).fill(null).map((_, i) => <LoadingRow key={i} />)
                : players.map((player, i) => (
                    <Row
                      key={player.pid}
                      player={player}
                      onUpdateName={() => handleUpdateName(player, i)}
                      onUpdateOwner={() => handleUpdateOwner(player, i)}
                      onUpdateTexture={() => setTextureUpdating(i)}
                      onDelete={() => handleDelete(player)}
                    />
                  ))}
            </tbody>
          </table>
        </div>
      ) : (
        <div className="card-body d-flex flex-wrap">
          {isLoading
            ? new Array(10).fill(null).map((_, i) => <LoadingCard key={i} />)
            : players.map((player, i) => (
                <Card
                  key={player.pid}
                  player={player}
                  onUpdateName={() => handleUpdateName(player, i)}
                  onUpdateOwner={() => handleUpdateOwner(player, i)}
                  onUpdateTexture={() => setTextureUpdating(i)}
                  onDelete={() => handleDelete(player)}
                />
              ))}
        </div>
      )}
      <div className="card-footer">
        <div className="float-right">
          <Pagination page={page} totalPages={totalPages} onChange={setPage} />
        </div>
      </div>
      <ModalUpdateTexture
        open={textureUpdating > -1}
        onSubmit={handleUpdateTexture}
        onClose={handleCloseModalUpdateTexture}
      />
    </div>
  )
}

export default hot(PlayersManagement)
