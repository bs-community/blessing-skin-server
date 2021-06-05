import React, { useState, useEffect, useMemo } from 'react'
import { hot } from 'react-hot-loader/root'
import { enableMapSet } from 'immer'
import { useImmer } from 'use-immer'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { toast, showModal } from '@/scripts/notify'
import Loading from '@/components/Loading'
import Pagination from '@/components/Pagination'
import type { Plugin } from './types'
import Row from './Row'

enableMapSet()

const PluginsMarket: React.FC = () => {
  const [plugins, setPlugins] = useImmer<Plugin[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [installings, setInstallings] = useImmer<Set<string>>(() => new Set())

  const searchedPlugins = useMemo(
    () =>
      plugins.filter(
        (plugin) =>
          plugin.name.includes(search) || plugin.title.includes(search),
      ),
    [plugins, search],
  )

  useEffect(() => {
    const getPlugins = async () => {
      setIsLoading(true)
      const plugins = await fetch.get<Plugin[]>('/admin/plugins/market/list')
      setPlugins(() => plugins)
      setTotalPages(Math.ceil(plugins.length / 10))
      setIsLoading(false)
    }
    getPlugins()
  }, [])

  const handleSearchChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const search = event.target.value
    setSearch(search)
    setPage(1)

    const searchedPlugins = plugins.filter(
      (plugin) => plugin.name.includes(search) || plugin.title.includes(search),
    )
    setTotalPages(Math.ceil(searchedPlugins.length / 10))
  }

  const handleInstall = async (plugin: Plugin, index: number) => {
    setInstallings((installings) => {
      installings.add(plugin.name)
    })

    const {
      code,
      message,
      data = { reason: [] },
    } = await fetch.post<fetch.ResponseBody<{ reason: string[] }>>(
      '/admin/plugins/market/download',
      {
        name: plugin.name,
      },
    )
    if (code === 0) {
      toast.success(message)
      setPlugins((plugins) => {
        plugins[index]!.can_update = false
        plugins[index]!.installed = plugins[index]!.version
      })
    } else {
      showModal({
        mode: 'alert',
        children: (
          <div>
            <p>{message}</p>
            <ul>
              {data.reason.map((t, i) => (
                <li key={i}>{t}</li>
              ))}
            </ul>
          </div>
        ),
      })
    }

    setInstallings((installings) => {
      installings.delete(plugin.name)
    })
  }

  const handleUpdate = async (plugin: Plugin, index: number) => {
    try {
      await showModal({
        text: t('admin.confirmUpdate', {
          plugin: plugin.title,
          old: plugin.installed,
          new: plugin.version,
        }),
      })
    } catch {
      return
    }

    handleInstall(plugin, index)
  }

  const pagedPlugins = searchedPlugins.slice((page - 1) * 10, page * 10)

  return (
    <div className="card">
      <div className="card-header">
        <input
          type="text"
          className="form-control"
          placeholder={t('vendor.datatable.search')}
          value={search}
          onChange={handleSearchChange}
        />
      </div>
      {isLoading ? (
        <div className="card-body">
          <Loading />
        </div>
      ) : searchedPlugins.length === 0 ? (
        <div className="card-body text-center">{t('general.noResult')}</div>
      ) : (
        <div className="card-body table-responsive p-0">
          <table className="table table-striped">
            <thead>
              <tr>
                <th>{t('admin.pluginTitle')}</th>
                <th>{t('admin.pluginDescription')}</th>
                <th>{t('admin.pluginAuthor')}</th>
                <th>{t('admin.pluginVersion')}</th>
                <th>{t('admin.pluginDependencies')}</th>
                <th>{t('admin.operationsTitle')}</th>
              </tr>
            </thead>
            <tbody>
              {pagedPlugins.map((plugin, i) => (
                <Row
                  key={plugin.name}
                  plugin={plugin}
                  isInstalling={installings.has(plugin.name)}
                  onInstall={() => handleInstall(plugin, (page - 1) * 10 + i)}
                  onUpdate={() => handleUpdate(plugin, (page - 1) * 10 + i)}
                />
              ))}
            </tbody>
          </table>
        </div>
      )}
      <div className="card-footer">
        <div className="float-right">
          <Pagination page={page} totalPages={totalPages} onChange={setPage} />
        </div>
      </div>
    </div>
  )
}

export default hot(PluginsMarket)
