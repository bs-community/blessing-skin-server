import React from 'react'
import { t } from '@/scripts/i18n'

const ViewerSkeleton: React.FC = () => (
  <div className="card">
    <div className="card-header">
      <div className="d-flex justify-content-between">
        <h3 className="card-title">
          <span>{t('general.texturePreview')}</span>
        </h3>
      </div>
    </div>
    <div className="card-body"></div>
  </div>
)

export default ViewerSkeleton
