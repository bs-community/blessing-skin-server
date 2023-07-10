import styled from '@emotion/styled'
import Skeleton from 'react-loading-skeleton'
import { Box } from './styles'
import clsx from 'clsx'

const isDarkMode = document.body.classList.contains('dark-mode')

const ShrinkedSkeleton = styled(Skeleton)<{ width?: string }>`
  width: ${(props) => props.width};
`

const LoadingCard: React.FC = () => (
  <Box className={clsx('info-box', { 'bg-gray-dark': isDarkMode })}>
    <div className="info-box-icon">
      <Skeleton circle height={50} width={50} />
    </div>
    <div className="info-box-content">
      <div className="row">
        <div className="col-10">
          <ShrinkedSkeleton width="120px" />
        </div>
        <div className="col-2"></div>
      </div>
      <div>
        <div>
          <ShrinkedSkeleton width="150px" />
        </div>
        <div>
          <ShrinkedSkeleton width="180px" />
        </div>
      </div>
    </div>
  </Box>
)

export default LoadingCard
