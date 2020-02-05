import React, { useState, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { trans } from '../../../scripts/i18n'
import * as fetch from '../../../scripts/net'
import { showModal, toast } from '../../../scripts/notify'
import Loading from '../../../components/Loading'
import Row from './Row'
import ModalCreate from './ModalCreate'
import { App } from './types'

type Exception = {
  message: string
}

const OAuth: React.FC = () => {
  const [apps, setApps] = useState<App[]>([])
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    const getApps = async () => {
      setIsLoading(true)
      const allApps = await fetch.get<App[]>('/oauth/clients')
      setApps(allApps)
      setIsLoading(false)
    }
    getApps()
  }, [])

  const handleAdd = async (name: string, redirect: string) => {
    const result = await fetch.post<App | Exception>('/oauth/clients', {
      name,
      redirect,
    })
    if ('id' in result) {
      setApps(apps => [...apps, result])
    } else {
      toast.error(result.message)
    }
  }

  const editName = async (app: App, index: number) => {
    let name: string
    try {
      ;({ value: name } = await showModal({
        mode: 'prompt',
        title: trans('user.oauth.name'),
        input: app.name,
      }))
    } catch {
      return
    }

    const result = await fetch.put<App | Exception>(
      `/oauth/clients/${app.id}`,
      { ...app, name },
    )
    if ('id' in result) {
      setApps(apps => {
        apps[index].name = name
        return apps.slice()
      })
    } else {
      toast.error(result.message)
    }
  }

  const editRedirect = async (app: App, index: number) => {
    let redirect: string
    try {
      ;({ value: redirect } = await showModal({
        mode: 'prompt',
        title: trans('user.oauth.redirect'),
        input: app.redirect,
      }))
    } catch {
      return
    }

    const result = await fetch.put<App | Exception>(
      `/oauth/clients/${app.id}`,
      { ...app, redirect },
    )
    if ('id' in result) {
      setApps(apps => {
        apps[index].redirect = redirect
        return apps.slice()
      })
    } else {
      toast.error(result.message)
    }
  }

  const handleDelete = async (app: App) => {
    try {
      await showModal({
        text: trans('user.oauth.confirmRemove'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    await fetch.del(`/oauth/clients/${app.id}`)
    setApps(apps => apps.filter(a => a.id !== app.id))
  }

  return (
    <>
      <button
        className="btn btn-primary"
        data-toggle="modal"
        data-target="#modal-create"
      >
        {trans('user.oauth.create')}
      </button>
      <div className="card mt-2">
        <div className="card-body p-0">
          <table className="table table-striped">
            <thead>
              <tr>
                <th>{trans('user.oauth.id')}</th>
                <th>{trans('user.oauth.name')}</th>
                <th>{trans('user.oauth.secret')}</th>
                <th>{trans('user.oauth.redirect')}</th>
                <th>{trans('admin.operationsTitle')}</th>
              </tr>
            </thead>
            <tbody>
              {apps.length === 0 ? (
                <tr>
                  <td className="text-center" colSpan={5}>
                    {isLoading ? <Loading /> : 'Nothing here.'}
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
      <ModalCreate onCreate={handleAdd} />
    </>
  )
}

export default hot(OAuth)
