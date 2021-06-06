/** @jsxImportSource @emotion/react */
import React, { useState, useEffect, useRef } from 'react'
import { css } from '@emotion/react'
import styled from '@emotion/styled'
import * as skinview3d from 'skinview3d'
import { t } from '@/scripts/i18n'
import * as cssUtils from '@/styles/utils'
import * as breakpoints from '@/styles/breakpoints'
import SkinSteve from '../../../misc/textures/steve.png'
import bg1 from '../../../misc/backgrounds/1.webp'
import bg2 from '../../../misc/backgrounds/2.webp'
import bg3 from '../../../misc/backgrounds/3.webp'
import bg4 from '../../../misc/backgrounds/4.webp'
import bg5 from '../../../misc/backgrounds/5.webp'
import bg6 from '../../../misc/backgrounds/6.webp'
import bg7 from '../../../misc/backgrounds/7.webp'

const backgrounds = [bg1, bg2, bg3, bg4, bg5, bg6, bg7]
export const PICTURES_COUNT = backgrounds.length

interface Props {
  skin?: string
  cape?: string
  isAlex: boolean
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

const ActionButton = styled.i`
  display: inline;
  padding: 0.5em 0.5em;
  &:hover {
    color: #555;
    cursor: pointer;
  }
`

const cssCardBody = css`
  background-repeat: no-repeat;
  background-position: center;
  background-size: cover;
`
const cssViewer = css`
  ${breakpoints.greaterThan(breakpoints.Breakpoint.lg)} {
    min-height: 500px;
  }

  canvas {
    cursor: move;
  }
`

const Viewer: React.FC<Props> = (props) => {
  const { initPositionZ = 70 } = props

  const viewRef: React.MutableRefObject<skinview3d.SkinViewer> = useRef(null!)
  const containerRef = useRef<HTMLDivElement>(null)
  const stuffRef = useRef(emptyStuff)

  const [paused, setPaused] = useState(false)
  const [running, setRunning] = useState(false)
  const [reset, setReset] = useState(0)
  const [background, setBackground] = useState(() =>
    document.body.classList.contains('dark-mode') ? '#6c757d' : '#fff',
  )
  const [bgPicture, setBgPicture] = useState(-1)

  const indicator = (() => {
    const { skin, cape } = props
    if (skin && cape) {
      return `${t('general.skin')} & ${t('general.cape')}`
    } else if (skin) {
      return t('general.skin')
    } else if (cape) {
      return t('general.cape')
    }
    return ''
  })()

  useEffect(() => {
    const container = containerRef.current!
    const viewer = new skinview3d.SkinViewer({
      domElement: container,
      width: container.clientWidth,
      height: container.clientHeight,
      skinUrl: props.skin || SkinSteve,
      capeUrl: props.cape || '',
      detectModel: false,
    })
    viewer.camera.position.z = initPositionZ
    viewer.playerObject.skin.slim = props.isAlex

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
    return () => {
      stuffRef.current.firstRun = true
    }
  }, [])

  useEffect(() => {
    const viewer = viewRef.current
    viewer.skinUrl = props.skin || SkinSteve
  }, [props.skin])

  useEffect(() => {
    const viewer = viewRef.current
    if (props.cape) {
      viewer.capeUrl = props.cape
    } else {
      viewer.playerObject.cape.visible = false
    }
  }, [props.cape])

  useEffect(() => {
    const viewer = viewRef.current
    viewer.playerObject.skin.slim = props.isAlex
  }, [props.isAlex])

  const togglePause = () => {
    setPaused((paused) => !paused)
    viewRef.current.animationPaused = !viewRef.current.animationPaused
  }

  const toggleRun = () => {
    setRunning((running) => !running)
    const { handles } = stuffRef.current
    handles.run.paused = !handles.run.paused
    handles.walk.paused = false
  }

  const toggleRotate = () => {
    const { handles } = stuffRef.current
    handles.rotate.paused = !handles.rotate.paused
  }

  const handleReset = () => {
    setReset((c) => c + 1)
  }

  const setWhite = () => setBackground('#fff')
  const setGray = () => setBackground('#6c757d')
  const setBlack = () => setBackground('#000')
  const setPrevPicture = () => {
    let index = bgPicture
    if (bgPicture <= 0) {
      index = PICTURES_COUNT - 1
    } else {
      index -= 1
    }
    setBgPicture(index)
    setBackground(`url('${backgrounds[index]}')`)
  }
  const setNextPicture = () => {
    let index = bgPicture
    if (bgPicture >= PICTURES_COUNT - 1) {
      index = 0
    } else {
      index += 1
    }
    setBgPicture(index)
    setBackground(`url('${backgrounds[index]}')`)
  }

  const backgroundStyle = background.startsWith('#')
    ? { backgroundColor: background }
    : { backgroundImage: background }

  return (
    <div className="card">
      <div className="card-header">
        <div className="d-flex justify-content-between">
          <h3 className="card-title">
            <span>{t('general.texturePreview')}</span>
            {props.showIndicator && (
              <span className="badge bg-olive ml-1">{indicator}</span>
            )}
          </h3>
          <div>
            <ActionButton
              className={`fas fa-${running ? 'walking' : 'running'}`}
              data-toggle="tooltip"
              data-placement="bottom"
              title={`${t('general.walk')} / ${t('general.run')}`}
              onClick={toggleRun}
            ></ActionButton>
            <ActionButton
              className="fas fa-redo-alt"
              data-toggle="tooltip"
              data-placement="bottom"
              title={t('general.rotation')}
              onClick={toggleRotate}
            ></ActionButton>
            <ActionButton
              className={`fas fa-${paused ? 'play' : 'pause'}`}
              data-toggle="tooltip"
              data-placement="bottom"
              title={t('general.pause')}
              onClick={togglePause}
            ></ActionButton>
            <ActionButton
              className="fas fa-stop"
              data-toggle="tooltip"
              data-placement="bottom"
              title={t('general.reset')}
              onClick={handleReset}
            ></ActionButton>
          </div>
        </div>
      </div>
      <div className="card-body" css={cssCardBody} style={backgroundStyle}>
        <div ref={containerRef} css={cssViewer}></div>
      </div>
      <div className="card-footer">
        <div className="mt-2 mb-3 d-flex">
          <div
            className="btn-color bg-white rounded-pill mr-2 elevation-2"
            title={t('colors.white')}
            onClick={setWhite}
          />
          <div
            className="btn-color bg-black rounded-pill mr-2 elevation-2"
            title={t('colors.black')}
            onClick={setBlack}
          />
          <div
            className="btn-color bg-gray rounded-pill mr-2 elevation-2"
            title={t('colors.gray')}
            onClick={setGray}
          />
          <div
            className="btn-color bg-green rounded-pill mr-2 elevation-2"
            css={cssUtils.center}
            title={t('colors.prev')}
            onClick={setPrevPicture}
          >
            <i className="fas fa-arrow-left"></i>
          </div>
          <div
            className="btn-color bg-green rounded-pill mr-2 elevation-2"
            css={cssUtils.center}
            title={t('colors.next')}
            onClick={setNextPicture}
          >
            <i className="fas fa-arrow-right"></i>
          </div>
        </div>
        {props.children}
      </div>
    </div>
  )
}

export default Viewer
