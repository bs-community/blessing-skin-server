import React, { useState, useEffect, useRef } from 'react'
import ReactDOM from 'react-dom'
import { hot } from 'react-hot-loader/root'
import { t } from '@/scripts/i18n'
import useBlessingExtra from '@/scripts/hooks/useBlessingExtra'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import { isAlex } from '@/scripts/textureUtils'
import { TextureType } from '@/scripts/types'
import ViewerSkeleton from '@/components/ViewerSkeleton'
import styles from './styles.module.scss'

const Previewer = React.lazy(() => import('@/components/Viewer'))

const container = document.createElement('div')

const Upload: React.FC = () => {
  const [name, setName] = useState('')
  const [type, setType] = useState<TextureType>('steve')
  const [isPrivate, setIsPrivate] = useState(false)
  const [isUploading, setIsUploading] = useState(false)
  const [file, setFile] = useState<File | null>(null)
  const [texture, setTexture] = useState('')
  const fileInput = useRef<HTMLInputElement>(null)
  const nameRule = useBlessingExtra<string>('rule')
  const contentPolicy = useBlessingExtra<string>('contentPolicy')
  const privacyNotice = useBlessingExtra<string>('privacyNotice')
  const award = useBlessingExtra<number>('award')
  const scorePublic = useBlessingExtra<number>('scorePublic')
  const scorePrivate = useBlessingExtra<number>('scorePrivate')
  const closetItemCost = useBlessingExtra<number>('closetItemCost')

  useEffect(() => {
    const mount = document.querySelector('#previewer')!
    mount.appendChild(container)

    return () => {
      mount.removeChild(container)
    }
  }, [])

  const handleNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setName(event.target.value)
  }

  const handleTypeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setType(event.target.value as TextureType)
  }

  const handlePrivateChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setIsPrivate(event.target.checked)
  }

  const invokeSelectFile = () => {
    fileInput.current!.click()
  }

  const handleSelectFile = async (
    event: React.ChangeEvent<HTMLInputElement>,
  ) => {
    const files = event.target.files!
    const [file] = files
    if (file) {
      setFile(file)
      if (!name && file.name.endsWith('.png')) {
        setName(file.name.slice(0, file.name.length - 4))
      }
      const texture = URL.createObjectURL(file)
      setTexture(texture)
      if (type !== 'cape') {
        setType((await isAlex(texture)) ? 'alex' : 'steve')
      }
    }
  }

  const handleUpload = async () => {
    if (!file) {
      toast.error(t('skinlib.emptyUploadFile'))
      return
    }

    if (!name) {
      toast.error(t('skinlib.emptyTextureName'))
      return
    }

    if (file.type !== 'image/png' && file.type !== 'image/x-png') {
      toast.error(t('skinlib.fileExtError'))
      return
    }

    const formData = new FormData()
    formData.append('name', name)
    formData.append('type', type)
    formData.append('file', file, file.name)
    formData.append('public', (!isPrivate).toString())

    setIsUploading(true)
    const { code, message, data: { tid } = { tid: 0 } } = await fetch.post<
      fetch.ResponseBody<{ tid: number }>
    >('/skinlib/upload', formData)
    setIsUploading(false)

    if (code === 0) {
      window.location.href = `${blessing.base_url}/skinlib/show/${tid}`
    } else if (code === 2) {
      try {
        await showModal({
          mode: 'confirm',
          text: message,
          okButtonText: t('user.viewInSkinlib'),
        })
        window.location.href = `${blessing.base_url}/skinlib/show/${tid}`
      } catch {
        //
      }
    } else {
      toast.error(message)
    }
  }

  const costRatio = isPrivate ? scorePrivate : scorePublic
  const size = file?.size ?? 0
  const scoreCost = (~~(size / 1024) || 1) * costRatio + closetItemCost

  return (
    <>
      <div className="card card-primary">
        <div className="card-body">
          <div className="form-group">
            <label htmlFor="texture-name">
              {t('skinlib.upload.texture-name')}
            </label>
            <input
              className="form-control"
              id="texture-name"
              type="text"
              placeholder={nameRule}
              value={name}
              onChange={handleNameChange}
            />
          </div>
          <div className="form-group">
            <label>{t('skinlib.upload.texture-type')}</label>
            <br />
            <label className="mr-4">
              <input
                type="radio"
                className="mr-1"
                name="type"
                value="steve"
                checked={type === 'steve'}
                onChange={handleTypeChange}
              />
              Steve
            </label>
            <label className="mr-4">
              <input
                type="radio"
                className="mr-1"
                name="type"
                value="alex"
                checked={type === 'alex'}
                onChange={handleTypeChange}
              />
              Alex
            </label>
            <label className="mr-4">
              <input
                type="radio"
                className="mr-1"
                name="type"
                value="cape"
                checked={type === 'cape'}
                onChange={handleTypeChange}
              />
              {t('general.cape')}
            </label>
          </div>
          <div className="form-group">
            <label htmlFor="select-file">
              {t('skinlib.upload.select-file')}
            </label>
            <div className="input-group">
              <div className="custom-file">
                <input
                  type="file"
                  className="custom-file-input"
                  id="select-file"
                  accept="image/png, image/x-png"
                  title={t('skinlib.upload.select-file')}
                  ref={fileInput}
                  onChange={handleSelectFile}
                />
                <label className={`custom-file-label ${styles.label}`}>
                  {file?.name}
                </label>
              </div>
              <div className="input-group-append">
                <button className="btn btn-default" onClick={invokeSelectFile}>
                  {t('skinlib.upload.select-file')}
                </button>
              </div>
            </div>
          </div>

          {contentPolicy && (
            <div
              className="callout callout-warning"
              dangerouslySetInnerHTML={{ __html: contentPolicy }}
            />
          )}
        </div>
        <div className="card-footer">
          <div className="container px-0 d-flex justify-content-between">
            <label
              className="mt-2"
              htmlFor="is-private"
              title={t('skinlib.upload.privacy-notice')}
            >
              <input
                type="checkbox"
                id="is-private"
                className="mr-1"
                checked={isPrivate}
                onChange={handlePrivateChange}
              />
              {t('skinlib.upload.set-as-private')}
            </label>
            <button
              className="btn btn-success"
              disabled={isUploading}
              onClick={handleUpload}
            >
              {isUploading ? (
                <>
                  <i className="fas fa-spinner fa-spin mr-1" />
                  <span>{t('skinlib.uploading')}</span>
                </>
              ) : (
                t('skinlib.upload.button')
              )}
            </button>
          </div>
          {file && (
            <div className="callout callout-success mt-3">
              <p>{t('skinlib.upload.cost', { score: scoreCost })}</p>
            </div>
          )}
          {isPrivate && (
            <div className="callout callout-info mt-3">
              <p>{privacyNotice}</p>
            </div>
          )}
          {!isPrivate && award > 0 && (
            <div className="callout callout-success mt-3">
              <p>{t('skinlib.upload.award', { score: award })}</p>
            </div>
          )}
        </div>
      </div>
      {ReactDOM.createPortal(
        <React.Suspense fallback={<ViewerSkeleton />}>
          <Previewer
            skin={type !== 'cape' ? texture : undefined}
            cape={type === 'cape' ? texture : undefined}
            isAlex={type === 'alex'}
          />
        </React.Suspense>,
        container,
      )}
    </>
  )
}

export default hot(Upload)
