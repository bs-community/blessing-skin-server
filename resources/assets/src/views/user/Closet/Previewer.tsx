import React from 'react'
import ReactDOM from 'react-dom'
import useMount from '@/scripts/hooks/useMount'
import ViewerSkeleton from '@/components/ViewerSkeleton'

const Viewer = React.lazy(() => import('@/components/Viewer'))

interface Props {
  skin?: string
  cape?: string
  isAlex: boolean
}

const Previewer: React.FC<Props> = (props) => {
  const container = useMount('#previewer')

  const skin = props.skin ? `${blessing.base_url}/textures/${props.skin}` : ''
  const cape = props.cape ? `${blessing.base_url}/textures/${props.cape}` : ''

  return ReactDOM.createPortal(
    <React.Suspense fallback={<ViewerSkeleton />}>
      <Viewer skin={skin} cape={cape} isAlex={props.isAlex} showIndicator>
        {props.children}
      </Viewer>
    </React.Suspense>,
    container,
  )
}

export default Previewer
