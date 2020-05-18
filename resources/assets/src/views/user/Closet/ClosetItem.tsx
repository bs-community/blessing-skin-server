import React from 'react'
import styled from '@emotion/styled'
import { t } from '@/scripts/i18n'
import { ClosetItem } from '@/scripts/types'
import setAsAvatar from './setAsAvatar'

const Card = styled.div`
  width: 235px;
  transition-property: box-shadow;
  transition-duration: 0.3s;

  &:hover {
    cursor: pointer;
  }

  .card-body {
    background-color: #eff1f0;
  }
`
const DropdownButton = styled.i`
  :hover {
    color: #000;
  }
`

interface Props {
  item: ClosetItem
  selected: boolean
  onClick(item: ClosetItem): void
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
            <span
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <DropdownButton className="fas fa-cog text-gray" />
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
    </Card>
  )
}

export default ClosetItem
