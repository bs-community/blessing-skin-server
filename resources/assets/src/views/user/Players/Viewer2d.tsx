import styled from '@emotion/styled'
import { t } from '@/scripts/i18n'

const TexturePreview = styled.div`
  display: flex;
  justify-content: space-between;
  width: 80%;

  img {
    max-height: 64px;
    width: 64px;
  }
`

interface Props {
  skin: string
  cape: string
}

const Viewer2d: React.FC<Props> = (props) => {
  return (
    <div className="card">
      <div className="card-header">
        <h3 className="card-title">{t('general.texturePreview')}</h3>
      </div>
      <div className="card-body">
        <TexturePreview className="mb-5">
          <span>{t('general.skin')}</span>
          {props.skin ? (
            <img src={props.skin} alt={t('general.skin')} />
          ) : (
            <span>{t('user.player.texture-empty')}</span>
          )}
        </TexturePreview>
        <TexturePreview className="mt-5">
          <span>{t('general.cape')}</span>
          {props.cape ? (
            <img src={props.cape} alt={t('general.cape')} />
          ) : (
            <span>{t('user.player.texture-empty')}</span>
          )}
        </TexturePreview>
      </div>
      <div className="card-footer">{props.children}</div>
    </div>
  )
}

export default Viewer2d
