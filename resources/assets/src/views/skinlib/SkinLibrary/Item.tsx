import React from 'react'
import { t } from '@/scripts/i18n'
import { LibraryItem } from './types'
import { humanizeType } from './utils'
import styles from './Item.module.scss'

interface Props {
  item: LibraryItem
  liked: boolean
  onAdd(texture: LibraryItem): Promise<void>
  onRemove(texture: LibraryItem): Promise<void>
  onUploaderClick(uploader: number): void
}

const Item: React.FC<Props> = (props) => {
  const { item } = props

  const link = `${blessing.base_url}/skinlib/show/${item.tid}`
  const preview = `${blessing.base_url}/preview/${item.tid}?height=150`

  const handleUploaderClick = (event: React.MouseEvent) => {
    event.preventDefault()
    props.onUploaderClick(item.uploader)
  }

  const handleHeartClick = () => {
    props.liked ? props.onRemove(item) : props.onAdd(item)
  }

  return (
    <div className="ml-3 mr-2 mb-2">
      <div className={`card ${styles.card}`}>
        <div className={`card-body ${styles.image}`}>
          {item.public || (
            <div className="ribbon-wrapper">
              <div className="ribbon bg-pink">{t('skinlib.private')}</div>
            </div>
          )}
          <a href={link} target="_blank">
            <img src={preview} alt={item.name} className="card-img-top" />
          </a>
        </div>
        <div className="card-footer">
          <a
            className={`d-block mb-1 ${styles.truncate}`}
            title={item.name}
            href={link}
            target="_blank"
          >
            {item.name}
          </a>
          <div className="d-flex justify-content-between">
            <div>
              <span className="badge bg-teal py-1 mr-1">
                {humanizeType(item.type)}
              </span>
              <a
                className="badge bg-indigo py-1 cursor-pointer"
                title={t('skinlib.show.uploader')}
                onClick={handleUploaderClick}
              >
                {item.nickname}
              </a>
            </div>
            <a
              className={`cursor-pointer ${styles.like}`}
              onClick={handleHeartClick}
              tabIndex={-1}
              data-liked={props.liked}
            >
              <i className="fas fa-heart mr-1"></i>
              {item.likes}
            </a>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Item
