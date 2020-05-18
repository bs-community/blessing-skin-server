/** @jsx jsx */
import { jsx } from '@emotion/core'
import styled from '@emotion/styled'
import { t } from '@/scripts/i18n'
import * as cssUtils from '@/styles/utils'
import { LibraryItem } from './types'
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

interface ButtonLikeProps {
  liked: boolean
}
const ButtonLike = styled.a<ButtonLikeProps>`
  color: ${(props) => (props.liked ? '#dc3545' : '#6c757d')};
  &:hover {
    color: ${(props) => (props.liked ? '#dc3545' : '#343a40')};
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
            className="d-block mb-1 truncate-text"
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
                className="badge bg-indigo py-1"
                css={cssUtils.pointerCursor}
                title={t('skinlib.show.uploader')}
                onClick={handleUploaderClick}
              >
                {item.nickname}
              </a>
            </div>
            <ButtonLike
              liked={props.liked}
              css={cssUtils.pointerCursor}
              tabIndex={-1}
              onClick={handleHeartClick}
            >
              <i className="fas fa-heart mr-1"></i>
              {item.likes}
            </ButtonLike>
          </div>
        </div>
      </Card>
    </div>
  )
}

export default Item
