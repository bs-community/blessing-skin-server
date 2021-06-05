import React from 'react'
import { t } from '@/scripts/i18n'
import type { ClosetItem as ClosetItemType } from '@/scripts/types'
import setAsAvatar from './setAsAvatar'
import { Card, DropdownButton } from './styles'

interface Props {
  item: ClosetItemType
  selected: boolean
  onClick(item: ClosetItemType): void
  onRename(): void
  onRemove(): void
}

const ClosetItem: React.FC<Props> = (props) => {
  const { item } = props

  const handleItemClick = () => {
    props.onClick(item)
  }

  const handleSetAsAvatar = () => setAsAvatar(item.tid)

  return (
    <Card className={`card mr-3 mb-3 ${props.selected ? 'shadow' : ''}`}>
      <div className="card-body" onClick={handleItemClick}>
        <img
          src={`${blessing.base_url}/preview/${item.tid}?height=150`}
          alt={item.pivot.item_name}
          className="card-img-top"
        />
      </div>
      <div className="card-footer pb-2 pt-2 pl-1 pr-1">
        <div className="container d-flex justify-content-between">
          <span className="text-truncate" title={item.pivot.item_name}>
            {item.pivot.item_name}
          </span>
          <span className="d-inline-block drop-down">
            <DropdownButton
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <i className="fas fa-cog" />
            </DropdownButton>
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
    </Card>
  )
}

export default ClosetItem
