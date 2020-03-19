import React, { useEffect } from 'react'
import ReactDOM from 'react-dom'
import ViewerSkeleton from '@/components/ViewerSkeleton'

const Viewer = React.lazy(() => import('@/components/Viewer'))

interface Props {
  skin?: string
  cape?: string
  isAlex: boolean
}

const container = document.createElement('div')

const Previewer: React.FC<Props> = props => {
  useEffect(() => {
    const mount = document.querySelector('#previewer')!
    mount.appendChild(container)

    return () => {
      mount.removeChild(container)
    }
  }, [])

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
