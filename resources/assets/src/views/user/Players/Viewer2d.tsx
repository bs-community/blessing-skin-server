import React from 'react'
import { t } from '@/scripts/i18n'
import styles from './Viewer2d.module.scss'

interface Props {
  skin: string
  cape: string
}

const Viewer2d: React.FC<Props> = props => {
  return (
    <div className="card">
      <div className="card-header">
        <h3 className="card-title">{t('general.texturePreview')}</h3>
      </div>
      <div className="card-body">
        <div className={`d-flex justify-content-between mb-5 ${styles.line}`}>
          <span>{t('general.skin')}</span>
          {props.skin ? (
            <img
              src={props.skin}
              className={styles.texture}
              alt={t('general.skin')}
            />
          ) : (
            <span>{t('user.player.texture-empty')}</span>
          )}
        </div>
        <div className={`d-flex justify-content-between mt-5 ${styles.line}`}>
          <span>{t('general.cape')}</span>
          {props.cape ? (
            <img
              src={props.cape}
              className={styles.texture}
              alt={t('general.cape')}
            />
          ) : (
            <span>{t('user.player.texture-empty')}</span>
          )}
        </div>
      </div>
      <div className="card-footer">{props.children}</div>
    </div>
  )
}

export default Viewer2d
