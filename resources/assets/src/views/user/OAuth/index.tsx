import React, { useState, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import Loading from '@/components/Loading'
import Row from './Row'
import ModalCreate from './ModalCreate'
import type { App } from './types'

type Exception = {
  message: string
}

const OAuth: React.FC = () => {
  const [apps, setApps] = useState<App[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [showModalCreate, setShowModalCreate] = useState(false)

  useEffect(() => {
    const getApps = async () => {
      setIsLoading(true)
      const allApps = await fetch.get<App[]>('/oauth/clients')
      setApps(allApps)
      setIsLoading(false)
    }
    getApps()
  }, [])

  const handleShowModalCreate = () => setShowModalCreate(true)

  const handleCloseModalCreate = () => setShowModalCreate(false)

  const handleAdd = async (name: string, redirect: string) => {
    const result = await fetch.post<App | Exception>('/oauth/clients', {
      name,
      redirect,
    })
    if ('id' in result) {
      setApps((apps) => [...apps, result])
    } else {
      toast.error(result.message)
    }
  }

  const editName = async (app: App, index: number) => {
    let name: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        title: t('user.oauth.name'),
        input: app.name,
      })
      name = value
    } catch {
      return
    }

    const result = await fetch.put<App | Exception>(
      `/oauth/clients/${app.id}`,
      { ...app, name },
    )
    if ('id' in result) {
      setApps((apps) => {
        apps[index] = { ...app, name }
        return apps.slice()
      })
    } else {
      toast.error(result.message)
    }
  }

  const editRedirect = async (app: App, index: number) => {
    let redirect: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        title: t('user.oauth.redirect'),
        input: app.redirect,
      })
      redirect = value
    } catch {
      return
    }

    const result = await fetch.put<App | Exception>(
      `/oauth/clients/${app.id}`,
      { ...app, redirect },
    )
    if ('id' in result) {
      setApps((apps) => {
        apps[index] = { ...app, redirect }
        return apps.slice()
      })
    } else {
      toast.error(result.message)
    }
  }

  const handleDelete = async (app: App) => {
    try {
      await showModal({
        text: t('user.oauth.confirmRemove'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    await fetch.del(`/oauth/clients/${app.id}`)
    setApps((apps) => apps.filter((a) => a.id !== app.id))
  }

  return (
    <>
      <button className="btn btn-primary" onClick={handleShowModalCreate}>
        {t('user.oauth.create')}
      </button>
      <div className="card mt-2">
        <div className="card-body p-0">
          <table className="table table-striped">
            <thead>
              <tr>
                <th>{t('user.oauth.id')}</th>
                <th>{t('user.oauth.name')}</th>
                <th>{t('user.oauth.secret')}</th>
                <th>{t('user.oauth.redirect')}</th>
                <th>{t('admin.operationsTitle')}</th>
              </tr>
            </thead>
            <tbody>
              {apps.length === 0 ? (
                <tr>
                  <td className="text-center" colSpan={5}>
                    {isLoading ? <Loading /> : t('general.noResult')}
                  </td>
                </tr>
              ) : (
                apps.map((app, i) => (
                  <Row
                    key={app.id}
                    app={app}
                    onEditName={() => editName(app, i)}
                    onEditRedirect={() => editRedirect(app, i)}
                    onDelete={() => handleDelete(app)}
                  />
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
      <ModalCreate
        show={showModalCreate}
        onCreate={handleAdd}
        onClose={handleCloseModalCreate}
      />
    </>
  )
}

export default hot(OAuth)
