import React, { useState, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { trans } from '../../../scripts/i18n'
import * as fetch from '../../../scripts/net'
import { toast, showModal } from '../../../scripts/notify'
import Loading from '../../../components/Loading'
import alertUnresolved from '../../../components/mixins/alertUnresolvedPlugins'
import InfoBox from './InfoBox'
import { Plugin } from './types'

const PluginsManagement: React.FC = () => {
  const [loading, setLoading] = useState(false)
  const [plugins, setPlugins] = useState<Plugin[]>([])
  useEffect(() => {
    const getPlugins = async () => {
      setLoading(true)
      setPlugins(await fetch.get('/admin/plugins/data'))
      setLoading(false)
    }
    getPlugins()
  }, [])

  const handleEnable = async (plugin: Plugin, i: number) => {
    const {
      code,
      message,
      data: { reason } = { reason: [] },
    }: fetch.ResponseBody<{
      reason: string[]
    }> = await fetch.post('/admin/plugins/manage', {
      action: 'enable',
      name: plugin.name,
    })
    if (code === 0) {
      toast.success(message)
      setPlugins(plugins => {
        plugins.splice(i, 1, { ...plugin, enabled: true })
        return plugins.slice()
      })
    } else {
      alertUnresolved(message, reason)
    }
  }

  const handleDisable = async (plugin: Plugin, i: number) => {
    const { code, message } = await fetch.post('/admin/plugins/manage', {
      action: 'disable',
      name: plugin.name,
    })
    if (code === 0) {
      toast.success(message)
      setPlugins(plugins => {
        plugins.splice(i, 1, { ...plugin, enabled: false })
        return plugins.slice()
      })
    } else {
      toast.error(message)
    }
  }

  const handleDelete = async (plugin: Plugin) => {
    try {
      await showModal({
        title: plugin.title,
        text: trans('admin.confirmDeletion'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    const { code, message } = await fetch.post('/admin/plugins/manage', {
      action: 'delete',
      name: plugin.name,
    })
    if (code === 0) {
      const { name } = plugin
      setPlugins(plugins => plugins.filter(plugin => plugin.name !== name))
      toast.success(message)
    } else {
      toast.error(message)
    }
  }

  return loading ? (
    <Loading />
  ) : (
    <div className="d-flex flex-wrap">
      {plugins.map((plugin, i) => (
        <InfoBox
          key={plugin.name}
          plugin={plugin}
          onEnable={plugin => handleEnable(plugin, i)}
          onDisable={plugin => handleDisable(plugin, i)}
          onDelete={handleDelete}
          baseUrl={blessing.base_url}
        />
      ))}
    </div>
  )
}

export default hot(PluginsManagement)
