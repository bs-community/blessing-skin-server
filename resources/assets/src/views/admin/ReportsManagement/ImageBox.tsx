import React from 'react'
import styled from '@emotion/styled'
import { t } from '@/scripts/i18n'
import type { Texture } from '@/scripts/types'
import { Report, Status } from './types'

const Card = styled.div`
  width: 240px;
  transition-property: box-shadow;
  transition-duration: 0.3s;

  .card-body {
    flex: unset;
    display: flex;
    justify-content: center;
  }

  img {
    cursor: pointer;
    width: 170px;
    height: 170px;
  }

  .card-footer {
    flex: 1 1 auto;
    * {
      margin: 2.5px 0;
    }
  }
`

interface Props {
  report: Report
  onClick(texture: Texture | null): void
  onBan(): void
  onDelete(): void
  onReject(): void
}

const ImageBox: React.FC<Props> = (props) => {
  const { report } = props

  const handleImageClick = () => props.onClick(report.texture)

  return (
    <Card className="card mr-3 mb-3">
      <div className="card-header">
        <b>
          {t('skinlib.show.uploader')}
          {': '}
        </b>
        <span className="mr-1">{report.texture_uploader?.nickname}</span>
        (UID: {report.uploader})
      </div>
      <div className="card-body">
        <img
          src={`${blessing.base_url}/preview/${report.tid}?height=150`}
          alt={report.tid.toString()}
          className="card-img-top"
          onClick={handleImageClick}
        />
      </div>
      <div className="card-footer">
        <div className="d-flex justify-content-between">
          <div>
            {report.status === Status.Pending ? (
              <span className="badge bg-warning">{t('report.status.0')}</span>
            ) : report.status === Status.Resolved ? (
              <span className="badge bg-success">{t('report.status.1')}</span>
            ) : (
              <span className="badge bg-danger">{t('report.status.2')}</span>
            )}
            <span className="badge bg-info ml-1">TID: {report.tid}</span>
          </div>
          <div className="dropdown">
            <a
              className="text-gray"
              href="#"
              data-toggle="dropdown"
              aria-expanded="false"
            >
              <i className="fas fa-cog"></i>
            </a>
            <div className="dropdown-menu dropdown-menu-right">
              <a
                href={`${blessing.base_url}/skinlib/show/${report.tid}`}
                className="dropdown-item"
                target="_blank"
              >
                <i className="fas fa-share-square mr-2"></i>
                {t('user.viewInSkinlib')}
              </a>
              <a href="#" className="dropdown-item" onClick={props.onBan}>
                <i className="fas fa-user-slash mr-2"></i>
                {t('report.ban')}
              </a>
              <a
                href="#"
                className="dropdown-item dropdown-item-danger"
                onClick={props.onDelete}
              >
                <i className="fas fa-trash mr-2"></i>
                {t('skinlib.show.delete-texture')}
              </a>
              <a href="#" className="dropdown-item" onClick={props.onReject}>
                <i className="fas fa-thumbs-down mr-2"></i>
                {t('report.reject')}
              </a>
            </div>
          </div>
        </div>
        <div>
          <b>
            {t('report.reporter')}
            {': '}
          </b>
          <span className="mr-1">{report.informer?.nickname}</span>
          (UID: {report.reporter})
        </div>
        <details>
          <summary className="text-truncate">
            <b>
              {t('report.reason')}
              {': '}
            </b>
            {report.reason}
          </summary>
          <div>{report.reason}</div>
          <div>
            <small>
              {t('report.time')}
              {': '}
              {report.report_at}
            </small>
          </div>
        </details>
      </div>
    </Card>
  )
}

export default ImageBox
