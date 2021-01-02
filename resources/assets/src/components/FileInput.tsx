/** @jsxImportSource @emotion/react */
import { useRef } from 'react'
import { css } from '@emotion/react'
import { t } from '@/scripts/i18n'

const hideRawBrowseButton = css`
  ::after {
    display: none;
  }
`

interface Props {
  file: File | null
  accept?: string
  onChange(event: React.ChangeEvent<HTMLInputElement>): void
}

const FileInput: React.FC<Props> = (props) => {
  const ref = useRef<HTMLInputElement>(null)

  const handleClick = () => {
    ref.current!.click()
  }

  return (
    <div className="form-group">
      <label htmlFor="select-file">{t('skinlib.upload.select-file')}</label>
      <div className="input-group">
        <div className="custom-file">
          <input
            type="file"
            className="custom-file-input"
            id="select-file"
            accept={props.accept}
            title={t('skinlib.upload.select-file')}
            ref={ref}
            onChange={props.onChange}
          />
          <label className="custom-file-label" css={hideRawBrowseButton}>
            {props.file?.name}
          </label>
        </div>
        <div className="input-group-append">
          <button className="btn btn-default" onClick={handleClick}>
            {t('skinlib.upload.select-file')}
          </button>
        </div>
      </div>
    </div>
  )
}

export default FileInput
