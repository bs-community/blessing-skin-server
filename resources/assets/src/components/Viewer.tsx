import React, { useState, useEffect, useRef } from 'react'
import * as skinview3d from 'skinview3d'
import { trans } from '../scripts/i18n'
import styles from './Viewer.scss'
import SkinSteve from '../../../misc/textures/steve.png'

interface Props {
  skin?: string
  cape?: string
  model?: 'steve' | 'alex'
  showIndicator?: boolean
  initPositionZ?: number
}

type ViewerStuff = {
  handles: {
    walk: skinview3d.AnimationHandle
    run: skinview3d.AnimationHandle
    rotate: skinview3d.AnimationHandle
  }
  control: skinview3d.OrbitControls
  firstRun: boolean
}

const emptyStuff: ViewerStuff = {
  handles: {
    walk: {} as skinview3d.AnimationHandle,
    run: {} as skinview3d.AnimationHandle,
    rotate: {} as skinview3d.AnimationHandle,
  },
  control: {} as skinview3d.OrbitControls,
  firstRun: true,
}

const Viewer: React.FC<Props> = props => {
  const viewRef: React.MutableRefObject<skinview3d.SkinViewer> = useRef(null!)
  const containerRef = useRef<HTMLDivElement>(null)
  const stuffRef = useRef(emptyStuff)

  const [paused, setPaused] = useState(false)
  const [reset, setReset] = useState(0)
  const indicator = (() => {
    const { skin, cape } = props
    if (skin && cape) {
      return `${trans('general.skin')} & ${trans('general.cape')}`
    } else if (skin) {
      return trans('general.skin')
    } else if (cape) {
      return trans('general.cape')
    }
    return ''
  })()

  useEffect(() => {
    const container = containerRef.current!
    const viewer = new skinview3d.SkinViewer({
      domElement: container,
      width: container.clientWidth,
      height: container.clientWidth,
      skinUrl: props.skin ?? SkinSteve,
      capeUrl: props.cape ?? '',
      detectModel: false,
    })
    viewer.camera.position.z = props.initPositionZ!

    const animation = new skinview3d.CompositeAnimation()
    stuffRef.current.handles = {
      walk: animation.add(skinview3d.WalkingAnimation),
      run: animation.add(skinview3d.RunningAnimation),
      rotate: animation.add(skinview3d.RotatingAnimation),
    }
    stuffRef.current.handles.run.paused = true
    // @ts-ignore
    viewer.animation = animation as skinview3d.Animation
    stuffRef.current.control = skinview3d.createOrbitControls(viewer)

    if (!stuffRef.current.firstRun) {
      const { handles } = stuffRef.current
      handles.walk.paused = true
      handles.run.paused = true
      handles.rotate.paused = true
      viewer.camera.position.z = 70
    }

    viewRef.current = viewer

    return () => {
      viewer.dispose()
      stuffRef.current.firstRun = false
    }
  }, [reset])

  useEffect(() => {
    const viewer = viewRef.current
    viewer.skinUrl = props.skin ?? SkinSteve
  }, [props.skin])

  useEffect(() => {
    const viewer = viewRef.current
    viewer.capeUrl = props.cape ?? ''
  }, [props.cape])

  useEffect(() => {
    const viewer = viewRef.current
    viewer.playerObject.skin.slim = props.model === 'alex'
  }, [props.model])

  const togglePause = () => {
    setPaused(paused => !paused)
    viewRef.current.animationPaused = !viewRef.current.animationPaused
  }

  const toggleRun = () => {
    const { handles } = stuffRef.current
    handles.run.paused = !handles.run.paused
    handles.walk.paused = false
  }

  const toggleRotate = () => {
    const { handles } = stuffRef.current
    handles.rotate.paused = !handles.rotate.paused
  }

  const handleReset = () => {
    setReset(c => c + 1)
  }

  return (
    <div className="card">
      <div className="card-header">
        <div className="d-flex justify-content-between">
          <h3 className="card-title">
            <span>{trans('general.texturePreview')}</span>
            {props.showIndicator && (
              <span className="badge bg-olive">{indicator}</span>
            )}
          </h3>
          <div className={styles.actions}>
            <i
              className="fas fa-forward"
              data-toggle="tooltip"
              data-placement="bottom"
              title={`${trans('general.walk')} / ${trans('general.run')}`}
              onClick={toggleRun}
            ></i>
            <i
              className="fas fa-redo-alt"
              data-toggle="tooltip"
              data-placement="bottom"
              title={trans('general.rotation')}
              onClick={toggleRotate}
            ></i>
            <i
              className={`fas fa-${paused ? 'play' : 'pause'}`}
              data-toggle="tooltip"
              data-placement="bottom"
              title={trans('general.pause')}
              onClick={togglePause}
            ></i>
            <i
              className="fas fa-stop"
              data-toggle="tooltip"
              data-placement="bottom"
              title={trans('general.reset')}
              onClick={handleReset}
            ></i>
          </div>
        </div>
      </div>
      <div className="card-body">
        <div ref={containerRef} className={styles.viewer}></div>
      </div>
      {props.children && <div className="card-footer">{props.children}</div>}
    </div>
  )
}

Viewer.defaultProps = {
  model: 'steve',
  initPositionZ: 70,
}

export default Viewer
