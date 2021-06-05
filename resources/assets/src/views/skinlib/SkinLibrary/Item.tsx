import React from 'react'
import styled from '@emotion/styled'
import { t } from '@/scripts/i18n'
import * as cssUtils from '@/styles/utils'
import type { LibraryItem } from './types'
import { humanizeType } from './utils'

const Card = styled.div`
  width: 245px;
  transition-property: box-shadow;
  transition-duration: 0.3s;
  &:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  }

  .card-body {
    background-color: #eff1f0;
  }

  img {
    height: 210px;
  }
`

const Badge = styled.span`
  padding-top: 0.4rem;
`

const NickNameBadge = styled(Badge)`
  ${cssUtils.pointerCursor}
  max-width: 100px;
`

interface ButtonLikeProps {
  liked: boolean
}
const ButtonLike = styled.a<ButtonLikeProps>`
  ${cssUtils.pointerCursor}

  i, span {
    color: ${(props) => (props.liked ? '#dc3545' : '#6c757d')};
    &:hover {
      color: ${(props) => (props.liked ? '#dc3545' : '#343a40')};
    }
  }
`

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
      <Card className="card">
        <div className="card-body">
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
            className="d-block mb-1 text-truncate"
            title={item.name}
            href={link}
            target="_blank"
          >
            {item.name}
          </a>
          <div className="d-flex justify-content-between">
            <div className="d-flex">
              <Badge className="badge bg-teal mr-1">
                {humanizeType(item.type)}
              </Badge>
              <NickNameBadge
                className="badge bg-indigo text-truncate"
                title={t('skinlib.show.uploader')}
                onClick={handleUploaderClick}
              >
                {item.nickname}
              </NickNameBadge>
            </div>
            <ButtonLike
              liked={props.liked}
              tabIndex={-1}
              onClick={handleHeartClick}
            >
              <i className="fas fa-heart mr-1"></i>
              <span>{item.likes}</span>
            </ButtonLike>
          </div>
        </div>
      </Card>
    </div>
  )
}

export default Item
