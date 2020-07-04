import React from 'react'
import styled from '@emotion/styled'
import Skeleton from 'react-loading-skeleton'
import { t } from '@/scripts/i18n'
import { Box, Icon, InfoTable } from './styles'

const ShrinkedSkeleton = styled(Skeleton)<{ width?: string }>`
  width: ${(props) => props.width};
`

const LoadingCard: React.FC = () => (
  <Box className="info-box">
    <Icon>
      <Skeleton circle height={50} width={50} />
    </Icon>
    <div className="info-box-content">
      <div className="row">
        <div className="col-10">
          <Skeleton width="140px" />
        </div>
        <div className="col-2"></div>
      </div>
      <div>
        <div>
          <Skeleton width="140px" />
        </div>
        <div>
          <Skeleton width="140px" />
        </div>
        <InfoTable className="row m-2 border-top border-bottom">
          <div className="col-sm-4 py-1 text-center">
            <b className="d-block">{t('general.user.score')}</b>
            <span className="d-block py-1">
              <ShrinkedSkeleton width="30%" />
            </span>
          </div>
          <div className="col-sm-4 py-1 text-center">
            <b className="d-block">{t('admin.permission')}</b>
            <span className="d-block py-1">
              <ShrinkedSkeleton width="30%" />
            </span>
          </div>
          <div className="col-sm-4 py-1 text-center">
            <b className="d-block">{t('admin.verification')}</b>
            <span className="d-block py-1">
              <ShrinkedSkeleton width="30%" />
            </span>
          </div>
        </InfoTable>
        <div>
          <Skeleton width="180px" />
        </div>
      </div>
    </div>
  </Box>
)

export default LoadingCard
