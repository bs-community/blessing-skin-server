import React, { useState, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { useImmer } from 'use-immer'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { toast, showModal } from '@/scripts/notify'
import FileInput from '@/components/FileInput'
import Loading from '@/components/Loading'
import InfoBox from './InfoBox'
import type { Plugin } from './types'

const PluginsManagement: React.FC = () => {
  const [isLoading, setIsLoading] = useState(true)
  const [plugins, setPlugins] = useImmer<Plugin[]>([])
  const [file, setFile] = useState<File | null>(null)
  const [isUploading, setIsUploading] = useState(false)
  const [url, setUrl] = useState('')
  const [isDownloading, setIsDownloading] = useState(false)

  useEffect(() => {
    const getPlugins = async () => {
      setIsLoading(true)
      const plugins = await fetch.get<Plugin[]>('/admin/plugins/data')
      setPlugins(() => plugins)
      setIsLoading(false)
    }
    getPlugins()
  }, [])

  const handleEnable = async (plugin: Plugin, i: number) => {
    const {
      code,
      message,
      data: { reason } = { reason: [] },
    } = await fetch.post<
      fetch.ResponseBody<{
        reason: string[]
      }>
    >('/admin/plugins/manage', {
      action: 'enable',
      name: plugin.name,
    })
    if (code === 0) {
      toast.success(message)
      setPlugins((plugins) => {
        plugins[i]!.enabled = true
      })
    } else {
      showModal({
        mode: 'alert',
        children: (
          <div>
            <p>{message}</p>
            <ul>
              {reason.map((t, i) => (
                <li key={i}>{t}</li>
              ))}
            </ul>
          </div>
        ),
      })
    }
  }

  const handleDisable = async (plugin: Plugin, i: number) => {
    const { code, message } = await fetch.post<fetch.ResponseBody>(
      '/admin/plugins/manage',
      {
        action: 'disable',
        name: plugin.name,
      },
    )
    if (code === 0) {
      toast.success(message)
      setPlugins((plugins) => {
        plugins[i]!.enabled = false
      })
    } else {
      toast.error(message)
    }
  }

  const handleDelete = async (plugin: Plugin) => {
    try {
      await showModal({
        title: plugin.title,
        text: t('admin.confirmDeletion'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    const { code, message } = await fetch.post<fetch.ResponseBody>(
      '/admin/plugins/manage',
      {
        action: 'delete',
        name: plugin.name,
      },
    )
    if (code === 0) {
      const { name } = plugin
      setPlugins((plugins) => plugins.filter((plugin) => plugin.name !== name))
      toast.success(message)
    } else {
      toast.error(message)
    }
  }

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFile(event.target.files![0]!)
  }

  const handleUrlChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setUrl(event.target.value)
  }

  const handleUpload = async () => {
    if (!file) {
      return
    }

    setIsUploading(true)
    const formData = new FormData()
    formData.append('file', file, file.name)
    const { code, message } = await fetch.post<fetch.ResponseBody>(
      '/admin/plugins/upload',
      formData,
    )

    setIsUploading(false)
    if (code === 0) {
      toast.success(message)
      setFile(null)

      const plugins = await fetch.get<Plugin[]>('/admin/plugins/data')
      setPlugins(() => plugins)
    } else {
      toast.error(message)
    }
  }

  const handleSubmitUrl = async () => {
    setIsDownloading(true)
    const { code, message } = await fetch.post<fetch.ResponseBody>(
      '/admin/plugins/wget',
      { url },
    )

    setIsDownloading(false)
    if (code === 0) {
      toast.success(message)
      setUrl('')

      const plugins = await fetch.get<Plugin[]>('/admin/plugins/data')
      setPlugins(() => plugins)
    } else {
      toast.error(message)
    }
  }

  const chunks = Array(Math.ceil(plugins.length / 2))
    .fill(null)
    .map((_, i) => plugins.slice(i * 2, (i + 1) * 2) as [Plugin, Plugin?])

  return (
    <div className="row">
      <div className="col-lg-8">
        {isLoading ? (
          <Loading />
        ) : plugins.length === 0 ? (
          t('general.noResult')
        ) : (
          chunks.map((chunk, i) => (
            <div className="row" key={`${chunk[0].name}&${chunk[1]?.name}`}>
              {(chunk as Plugin[]).map((plugin, j) => (
                <div className="col-md-6" key={plugin.name}>
                  <InfoBox
                    plugin={plugin}
                    onEnable={(plugin) => handleEnable(plugin, i * 2 + j)}
                    onDisable={(plugin) => handleDisable(plugin, i * 2 + j)}
                    onDelete={handleDelete}
                    baseUrl={blessing.base_url}
                  />
                </div>
              ))}
            </div>
          ))
        )}
      </div>
      <div className="col-lg-4">
        <div className="card card-primary card-outline">
          <div className="card-header">
            <h3 className="card-title">{t('admin.uploadArchive')}</h3>
          </div>
          <div className="card-body">
            <p>{t('admin.uploadArchiveNotice')}</p>
            <FileInput
              file={file}
              accept="application/zip"
              onChange={handleFileChange}
            />
          </div>
          <div className="card-footer">
            <button
              className="btn btn-primary float-right"
              disabled={isUploading}
              onClick={handleUpload}
            >
              {isUploading ? <Loading /> : t('general.submit')}
            </button>
          </div>
        </div>
        <div className="card card-primary card-outline">
          <div className="card-header">
            <h3 className="card-title">{t('admin.downloadRemote')}</h3>
          </div>
          <div className="card-body">
            <p>{t('admin.downloadRemoteNotice')}</p>
            <div className="form-group">
              <label htmlFor="zip-url">URL</label>
              <input
                type="text"
                id="zip-url"
                className="form-control"
                inputMode="url"
                value={url}
                onChange={handleUrlChange}
              />
            </div>
          </div>
          <div className="card-footer">
            <button
              className="btn btn-primary float-right"
              disabled={isDownloading}
              onClick={handleSubmitUrl}
            >
              {isDownloading ? <Loading /> : t('general.submit')}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default hot(PluginsManagement)
