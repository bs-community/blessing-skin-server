import React from 'react'
import styled from '@emotion/styled'
import Skeleton from 'react-loading-skeleton'

const TableRow = styled.tr`
  height: 64px;
`
const ThickSkeleton = styled(Skeleton)`
  line-height: 2;
`

const RowLoading: React.FC = () => (
  <TableRow>
    <td colSpan={3}>
      <ThickSkeleton />
    </td>
  </TableRow>
)

export default RowLoading
