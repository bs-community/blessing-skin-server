import React, { useState } from 'react'
import { t } from '@/scripts/i18n'
import { TextureType } from '@/scripts/types'
import Modal from '@/components/Modal'

interface Props {
  open: boolean
  onSubmit(type: 'skin' | 'cape', tid: number): void
  onClose(): void
}

const ModalUpdateTexture: React.FC<Props> = (props) => {
  const [type, setType] = useState<'skin' | 'cape'>('skin')
  const [tid, setTid] = useState('')

  const handleTypeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setType(event.target.value as 'skin' | 'cape')
  }

  const handleTidChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setTid(event.target.value)
  }

  const handleConfirm = () => {
    props.onSubmit(type, Number.parseInt(tid))
    setType('skin')
    setTid('')
  }

  const handleClose = () => {
    setType('skin')
    setTid('')
    props.onClose()
  }

  return (
    <Modal
      show={props.open}
      center
      title={t('admin.changeTexture')}
      onConfirm={handleConfirm}
      onClose={handleClose}
    >
      <div className="form-group">
        <label>{t('admin.textureType')}</label>
        <div>
          <label className="mr-5">
            <input
              className="mr-1"
              type="radio"
              value="skin"
              checked={type === 'skin'}
              onChange={handleTypeChange}
            />
            {t('general.skin')}
          </label>
          <label>
            <input
              className="mr-1"
              type="radio"
              value="cape"
              checked={type === TextureType.Cape}
              onChange={handleTypeChange}
            />
            {t('general.cape')}
          </label>
        </div>
      </div>
      <div className="form-group">
        <label htmlFor="update-texture-tid">TID</label>
        <input
          type="number"
          id="update-texture-tid"
          className="form-control"
          placeholder={t('admin.pidNotice')}
          value={tid}
          onChange={handleTidChange}
        />
      </div>
    </Modal>
  )
}

export default ModalUpdateTexture
