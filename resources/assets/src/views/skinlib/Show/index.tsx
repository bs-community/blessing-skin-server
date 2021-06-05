import React, { useState, useEffect } from 'react'
import { createPortal } from 'react-dom'
import { hot } from 'react-hot-loader/root'
import Skeleton from 'react-loading-skeleton'
import useBlessingExtra from '@/scripts/hooks/useBlessingExtra'
import useEmitMounted from '@/scripts/hooks/useEmitMounted'
import useMount from '@/scripts/hooks/useMount'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import { Texture, TextureType } from '@/scripts/types'
import urls from '@/scripts/urls'
import ButtonEdit from '@/components/ButtonEdit'
import ViewerSkeleton from '@/components/ViewerSkeleton'
import ModalApply from '@/views/user/Closet/ModalApply'
import removeClosetItem from '@/views/user/Closet/removeClosetItem'
import setAsAvatar from '@/views/user/Closet/setAsAvatar'
import addClosetItem from './addClosetItem'

export type Badge = {
  color: string
  text: string
}

const Previewer = React.lazy(() => import('@/components/Viewer'))

const Show: React.FC = () => {
  const [texture, setTexture] = useState<Texture>({} as Texture)
  const [isLoading, setIsLoading] = useState(true)
  const [showModalApply, setShowModalApply] = useState(false)
  const [liked, setLiked] = useState(false)
  const nickname = useBlessingExtra<string>('nickname')
  const isUploaderExists = useBlessingExtra<boolean>('uploaderExists')
  const currentUid = useBlessingExtra<number>('currentUid', 0)
  const isAdmin = useBlessingExtra<boolean>('admin')
  const badges = useBlessingExtra<Badge[]>('badges', [])
  const canBeDownloaded = useBlessingExtra<boolean>('download')
  const reportScore = useBlessingExtra<number>('report')
  const container = useMount('#previewer')

  useEmitMounted()

  useEffect(() => {
    const fetchInfo = async () => {
      const url = location.href
        .replace(blessing.base_url, '')
        .replace('skinlib/show', 'texture')

      const texture = await fetch.get<Texture>(url)
      setTexture(texture)
      setIsLoading(false)
    }
    fetchInfo()
  }, [])

  useEffect(() => {
    setLiked(blessing.extra.inCloset as boolean)
  }, [])

  const handleEditName = async () => {
    let name: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('skinlib.setNewTextureName'),
        input: texture.name,
        validator: (value: string) => {
          if (!value) {
            return t('skinlib.emptyNewTextureName')
          }
        },
      })
      name = value
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.texture.name(texture.tid),
      { name },
    )
    if (code === 0) {
      toast.success(message)
      setTexture((texture) => ({ ...texture, name }))
    } else {
      toast.error(message)
    }
  }

  const handleSwitchType = async () => {
    let type: TextureType
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('skinlib.setNewTextureModel'),
        input: texture.type,
        inputType: 'radios',
        choices: [
          { text: 'Steve', value: TextureType.Steve },
          { text: 'Alex', value: TextureType.Alex },
          { text: t('general.cape'), value: TextureType.Cape },
        ],
      })
      type = value as TextureType
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.texture.type(texture.tid),
      { type },
    )
    if (code === 0) {
      toast.success(message)
      setTexture((texture) => ({ ...texture, type }))
    } else {
      toast.error(message)
    }
  }

  const handleAddItemClick = async () => {
    const ok = await addClosetItem(texture)
    if (ok) {
      setTexture((texture) => ({ ...texture, likes: texture.likes + 1 }))
      setLiked(true)
    }
  }

  const handleRemoveItemClick = async () => {
    const ok = await removeClosetItem(texture.tid)
    if (ok) {
      setTexture((texture) => ({ ...texture, likes: texture.likes - 1 }))
      setLiked(false)
    }
  }

  const handleSetAsAvatar = () => setAsAvatar(texture.tid)

  const handleDownloadClick = () => {
    const a = document.createElement('a')
    a.href = `${blessing.base_url}/raw/${texture.tid}`
    a.download = `${texture.name}.png`
    a.click()
  }

  const handleReport = async () => {
    const prompt = (() => {
      if (reportScore > 0) {
        return t('skinlib.report.positive', { score: reportScore })
      } else if (reportScore < 0) {
        return t('skinlib.report.negative', { score: -reportScore })
      }
      return ''
    })()

    let reason: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        title: t('skinlib.report.title'),
        text: prompt,
        placeholder: t('skinlib.report.reason'),
      })
      reason = value
    } catch {
      return
    }

    const { code, message } = await fetch.post<fetch.ResponseBody>(
      '/skinlib/report',
      {
        tid: texture.tid,
        reason,
      },
    )
    if (code === 0) {
      toast.success(message)
    } else {
      toast.error(message)
    }
  }

  const handlePrivacyClick = async () => {
    try {
      await showModal({
        text: texture.public
          ? t('skinlib.setPrivateNotice')
          : t('skinlib.setPublicNotice'),
      })
    } catch {
      return
    }

    type Ok = { code: 0; message: string }
    type Err = { code: 1; message: string }
    type Duplicated = { code: 2; message: string; data: { tid: number } }

    const resp = await fetch.put<Ok | Err | Duplicated>(
      urls.texture.privacy(texture.tid),
    )
    const { code, message } = resp
    if (code === 0) {
      toast.success(message)
      setTexture((texture) => ({ ...texture, public: !texture.public }))
    } else if (resp.code === 2) {
      try {
        await showModal({
          mode: 'confirm',
          text: message,
          okButtonText: t('user.viewInSkinlib'),
        })
        window.location.href =
          blessing.base_url + urls.skinlib.show(resp.data.tid)
      } catch {
        //
      }
    } else {
      toast.error(message)
    }
  }

  const handleDeleteTextureClick = async () => {
    try {
      await showModal({
        text: t('skinlib.deleteNotice'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    const { code, message } = await fetch.del<fetch.ResponseBody>(
      urls.texture.delete(texture.tid),
    )
    if (code === 0) {
      toast.success(message)
      setTimeout(() => {
        window.location.href = `${blessing.base_url}/skinlib`
      }, 2000)
    } else {
      toast.error(message)
    }
  }

  const handleOpenModalApply = () => setShowModalApply(true)
  const handleCloseModalApply = () => setShowModalApply(false)

  const linkToUploader = (() => {
    const search = new URLSearchParams()
    search.append(
      'filter',
      texture.type === TextureType.Cape ? TextureType.Cape : 'skin',
    )
    search.append('uploader', texture.uploader?.toString())

    return `${blessing.base_url}/skinlib?${search.toString()}`
  })()

  const canEdit = currentUid === texture.uploader || isAdmin
  const textureUrl = `${blessing.base_url}/textures/${texture.hash}`

  return (
    <>
      {container &&
        createPortal(
          <React.Suspense fallback={<ViewerSkeleton />}>
            <Previewer
              {...{
                [texture.type === TextureType.Cape ? TextureType.Cape : 'skin']:
                  textureUrl,
              }}
              isAlex={texture.type === TextureType.Alex}
              initPositionZ={60}
            >
              {currentUid === 0 ? (
                <button
                  className="btn btn-outline-secondary"
                  title={t('skinlib.show.anonymous')}
                  disabled
                >
                  {t('skinlib.addToCloset')}
                </button>
              ) : (
                <div className="d-flex justify-content-between align-items-center">
                  <div>
                    {liked && (
                      <button
                        className="btn btn-outline-success mr-2"
                        onClick={handleOpenModalApply}
                      >
                        {t('skinlib.apply')}
                      </button>
                    )}
                    {liked ? (
                      <button
                        className="btn btn-outline-primary mr-2"
                        onClick={handleRemoveItemClick}
                      >
                        {t('skinlib.removeFromCloset')}
                      </button>
                    ) : (
                      <button
                        className="btn btn-outline-primary mr-2"
                        onClick={handleAddItemClick}
                      >
                        {t('skinlib.addToCloset')}
                      </button>
                    )}
                    {texture.type !== TextureType.Cape && (
                      <button
                        className="btn btn-outline-info mr-2"
                        onClick={handleSetAsAvatar}
                      >
                        {t('user.setAsAvatar')}
                      </button>
                    )}
                    {canBeDownloaded && (
                      <button
                        className="btn btn-outline-info mr-2"
                        onClick={handleDownloadClick}
                      >
                        {t('skinlib.show.download')}
                      </button>
                    )}
                    <button
                      className="btn btn-outline-info mr-2"
                      onClick={handleReport}
                    >
                      {t('skinlib.report.title')}
                    </button>
                  </div>
                  <div
                    className={liked ? 'text-red' : 'text-gray'}
                    title={t('skinlib.show.likes')}
                  >
                    <i className="fas fa-heart mr-1" />
                    <span>{texture.likes}</span>
                  </div>
                </div>
              )}
            </Previewer>
          </React.Suspense>,
          container,
        )}
      <div className="card card-primary">
        <div className="card-header">
          <h3 className="card-title">{t('skinlib.show.detail')}</h3>
        </div>
        <div className="card-body">
          <div className="container">
            <div className="row mt-2 mb-4">
              <div className="col-4">{t('skinlib.show.name')}</div>
              {isLoading ? (
                <div className="col-8">
                  <Skeleton />
                </div>
              ) : (
                <>
                  <div className="col-7 text-truncate" title={texture.name}>
                    {texture.name}
                  </div>
                  {canEdit && (
                    <div className="col-1">
                      <ButtonEdit
                        title={t('skinlib.show.edit')}
                        onClick={handleEditName}
                      />
                    </div>
                  )}
                </>
              )}
            </div>
            <div className="row my-4">
              <div className="col-4">{t('skinlib.show.model')}</div>
              {isLoading ? (
                <div className="col-8">
                  <Skeleton />
                </div>
              ) : (
                <>
                  <div className="col-7">
                    {texture.type === TextureType.Cape
                      ? t('general.cape')
                      : texture.type}
                  </div>
                  {canEdit && (
                    <div className="col-1">
                      <ButtonEdit
                        title={t('skinlib.show.edit')}
                        onClick={handleSwitchType}
                      />
                    </div>
                  )}
                </>
              )}
            </div>
            <div className="row my-4">
              <div className="col-4">Hash</div>
              <div
                className="col-8 text-truncate user-select-all"
                title={texture.hash}
              >
                {isLoading ? <Skeleton /> : texture.hash}
              </div>
            </div>
            <div className="row my-4">
              <div className="col-4">{t('skinlib.show.size')}</div>
              <div className="col-8">
                {isLoading ? <Skeleton /> : <span>{texture.size} KB</span>}
              </div>
            </div>
            <div className="row my-4">
              <div className="col-4">{t('skinlib.show.uploader')}</div>
              <div className="col-8 text-truncate">
                {isLoading ? (
                  <Skeleton />
                ) : isUploaderExists ? (
                  <>
                    <div>
                      <a href={linkToUploader} target="_blank">
                        {nickname}
                      </a>
                    </div>
                    <div>
                      {badges.map((badge) => (
                        <span
                          className={`badge bg-${badge.color} mr-2`}
                          key={badge.text}
                        >
                          {badge.text}
                        </span>
                      ))}
                    </div>
                  </>
                ) : (
                  nickname
                )}
              </div>
            </div>
            <div className="row mt-4 mb-2">
              <div className="col-4">{t('skinlib.show.upload-at')}</div>
              <div className="col-8">
                {isLoading ? <Skeleton /> : texture.upload_at}
              </div>
            </div>
          </div>
        </div>
      </div>
      {canEdit && (
        <div className="card card-warning">
          <div className="card-header">
            <h3 className="card-title">{t('admin.operationsTitle')}</h3>
          </div>
          <div className="card-body">
            <p>{t('skinlib.show.manage-notice')}</p>
          </div>
          <div className="card-footer">
            <div className="container d-flex justify-content-between">
              <button className="btn btn-warning" onClick={handlePrivacyClick}>
                {texture.public
                  ? t('skinlib.setAsPrivate')
                  : t('skinlib.setAsPublic')}
              </button>
              <button
                className="btn btn-danger"
                onClick={handleDeleteTextureClick}
              >
                {t('skinlib.show.delete-texture')}
              </button>
            </div>
          </div>
        </div>
      )}
      <ModalApply
        show={showModalApply}
        canAdd={false}
        {...{
          [texture.type === TextureType.Cape ? TextureType.Cape : 'skin']:
            texture.tid,
        }}
        onClose={handleCloseModalApply}
      />
    </>
  )
}

export default hot(Show)
