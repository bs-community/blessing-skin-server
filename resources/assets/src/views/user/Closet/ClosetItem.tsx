import React from 'react'
import { t } from '@/scripts/i18n'
import { ClosetItem } from '@/scripts/types'
import setAsAvatar from './setAsAvatar'
import styles from './ClosetItem.module.scss'

interface Props {
  item: ClosetItem
  selected: boolean
  onClick(item: ClosetItem): void
  onRename(): void
  onRemove(): void
}

const ClosetItem: React.FC<Props> = props => {
  const { item } = props

  const handleItemClick = () => {
    props.onClick(item)
  }

  const handleSetAsAvatar = () => setAsAvatar(item.tid)

  return (
    <div
      className={`card mr-3 mb-3 ${styles.item} ${
        props.selected ? 'shadow' : ''
      }`}
    >
      <div className={`card-body ${styles.bg}`} onClick={handleItemClick}>
        <img
          src={`${blessing.base_url}/preview/${item.tid}?height=150`}
          alt={item.pivot.item_name}
          className="card-img-top"
        />
      </div>
      <div className="card-footer pb-2 pt-2 pl-1 pr-1">
        <div className="container d-flex justify-content-between">
          <span className={styles.truncate} title={item.pivot.item_name}>
            {item.pivot.item_name}
          </span>
          <span className="d-inline-block drop-down">
            <span
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <i className={`fas fa-cog text-gray ${styles.icon}`} />
            </span>
            <div className="dropdown-menu">
              <a href="#" className="dropdown-item" onClick={props.onRename}>
                {t('user.renameItem')}
              </a>
              <a href="#" className="dropdown-item" onClick={props.onRemove}>
                {t('user.removeItem')}
              </a>
              <a
                href={`${blessing.base_url}/skinlib/show/${item.tid}`}
                className="dropdown-item"
                target="_blank"
              >
                {t('user.viewInSkinlib')}
              </a>
              <a href="#" className="dropdown-item" onClick={handleSetAsAvatar}>
                {t('user.setAsAvatar')}
              </a>
            </div>
          </span>
        </div>
      </div>
    </div>
  )
}

export default ClosetItem
