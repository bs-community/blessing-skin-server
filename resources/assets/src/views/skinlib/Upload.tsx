import React, { useState } from 'react'
import ReactDOM from 'react-dom'
import { hot } from 'react-hot-loader/root'
import { t } from '@/scripts/i18n'
import useBlessingExtra from '@/scripts/hooks/useBlessingExtra'
import useEmitMounted from '@/scripts/hooks/useEmitMounted'
import useMount from '@/scripts/hooks/useMount'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import { isAlex } from '@/scripts/textureUtils'
import { TextureType } from '@/scripts/types'
import urls from '@/scripts/urls'
import FileInput from '@/components/FileInput'
import ViewerSkeleton from '@/components/ViewerSkeleton'

const Previewer = React.lazy(() => import('@/components/Viewer'))

const Upload: React.FC = () => {
  const [name, setName] = useState('')
  const [type, setType] = useState(TextureType.Steve)
  const [isPrivate, setIsPrivate] = useState(false)
  const [isUploading, setIsUploading] = useState(false)
  const [file, setFile] = useState<File | null>(null)
  const [texture, setTexture] = useState('')
  const nameRule = useBlessingExtra<string>('rule')
  const contentPolicy = useBlessingExtra<string>('contentPolicy')
  const privacyNotice = useBlessingExtra<string>('privacyNotice')
  const award = useBlessingExtra<number>('award')
  const currentScore = useBlessingExtra<number>('score', 0)
  const scorePublic = useBlessingExtra<number>('scorePublic')
  const scorePrivate = useBlessingExtra<number>('scorePrivate')
  const closetItemCost = useBlessingExtra<number>('closetItemCost')

  const container = useMount('#previewer')

  useEmitMounted()

  const handleNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setName(event.target.value)
  }

  const handleTypeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setType(event.target.value as TextureType)
  }

  const handlePrivateChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setIsPrivate(event.target.checked)
  }

  const handleFileChange = async (
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
      if (type !== TextureType.Cape) {
        setType((await isAlex(texture)) ? TextureType.Alex : TextureType.Steve)
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
    formData.append('public', isPrivate ? '0' : '1')

    setIsUploading(true)
    const {
      code,
      message,
      data: { tid } = { tid: 0 },
    } = await fetch.post<fetch.ResponseBody<{ tid: number }>>(
      urls.texture.upload(),
      formData,
    )
    setIsUploading(false)

    if (code === 0) {
      window.location.href = blessing.base_url + urls.skinlib.show(tid)
    } else if (code === 2) {
      try {
        await showModal({
          mode: 'confirm',
          text: message,
          okButtonText: t('user.viewInSkinlib'),
        })
        window.location.href = blessing.base_url + urls.skinlib.show(tid)
      } catch {
        //
      }
    } else {
      toast.error(message)
    }
  }

  const costRatio = isPrivate ? scorePrivate : scorePublic
  const size = file?.size ?? 0
  const scoreCost = Math.ceil(size / 1024) * costRatio + closetItemCost

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
                checked={type === TextureType.Steve}
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
                checked={type === TextureType.Alex}
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
                checked={type === TextureType.Cape}
                onChange={handleTypeChange}
              />
              {t('general.cape')}
            </label>
          </div>
          <FileInput
            file={file}
            accept="image/png, image/x-png"
            onChange={handleFileChange}
          />

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
            <div
              className={`callout callout-${
                currentScore > scoreCost ? 'success' : 'danger'
              } mt-3`}
            >
              <div>{t('skinlib.upload.cost', { score: scoreCost })}</div>
              <div>
                {t('user.cur-score')}
                <span className="ml-1">{currentScore}</span>
              </div>
            </div>
          )}
          {isPrivate && (
            <div className="callout callout-info mt-3">{privacyNotice}</div>
          )}
          {!isPrivate && award > 0 && (
            <div className="callout callout-success mt-3">
              {t('skinlib.upload.award', { score: award })}
            </div>
          )}
        </div>
      </div>
      {container &&
        ReactDOM.createPortal(
          <React.Suspense fallback={<ViewerSkeleton />}>
            <Previewer
              skin={type !== TextureType.Cape ? texture : undefined}
              cape={type === TextureType.Cape ? texture : undefined}
              isAlex={type === TextureType.Alex}
            />
          </React.Suspense>,
          container,
        )}
    </>
  )
}

export default hot(Upload)
