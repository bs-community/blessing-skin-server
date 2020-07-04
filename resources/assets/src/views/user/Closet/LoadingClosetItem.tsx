import React from 'react'
import styled from '@emotion/styled'
import Skeleton from 'react-loading-skeleton'
import { Card, DropdownButton } from './styles'

const ItemNameSkeleton = styled(Skeleton)`
  width: 150px;
`

const LoadingClosetItem: React.FC = () => (
  <Card className="card mr-3 mb-3">
    <div className="card-body"></div>
    <div className="card-footer pb-2 pt-2 pl-1 pr-1">
      <div className="container d-flex justify-content-between">
        <ItemNameSkeleton />
        <span className="d-inline-block">
          <DropdownButton>
            <i className="fas fa-cog" />
          </DropdownButton>
        </span>
      </div>
    </div>
  </Card>
)

export default LoadingClosetItem
