import React, { useState } from 'react'
import ReactDOM from 'react-dom'
import useMount from '@/scripts/hooks/useMount'
import { t } from '@/scripts/i18n'
import ViewerSkeleton from '@/components/ViewerSkeleton'
import Viewer2d from './Viewer2d'

const Viewer3d = React.lazy(() => import('@/components/Viewer'))

interface Props {
  skin: string
  cape: string
  isAlex: boolean
}

const Previewer: React.FC<Props> = props => {
  const [is3d, setIs3d] = useState(true)

  const container = useMount('#previewer')

  const switchMode = () => setIs3d(is => !is)

  const switcher = (
    <button className="btn btn-default" onClick={switchMode}>
      {is3d ? t('user.switch2dPreview') : t('user.switch3dPreview')}
    </button>
  )

  const { skin, cape, isAlex } = props

  return ReactDOM.createPortal(
    is3d ? (
      <React.Suspense fallback={<ViewerSkeleton />}>
        <Viewer3d skin={skin} cape={cape} isAlex={isAlex}>
          {switcher}
        </Viewer3d>
      </React.Suspense>
    ) : (
      <Viewer2d skin={skin} cape={cape}>
        {switcher}
      </Viewer2d>
    ),
    container
  )
}

export default Previewer
