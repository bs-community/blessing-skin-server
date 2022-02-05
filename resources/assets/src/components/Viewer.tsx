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

type AnimationHandles = {
  walk: skinview3d.SubAnimationHandle | null
  run: skinview3d.SubAnimationHandle | null
  rotate: skinview3d.SubAnimationHandle | null
}

const animationHandles: AnimationHandles = {
  walk: null,
  run: null,
  rotate: null,
}

const ActionButton = styled.i`
  display: inline;
  padding: 0.5em 0.5em;
  &:hover {
    color: #555;
    cursor: pointer;
  }
`

const cssViewer = css`
  ${breakpoints.greaterThan(breakpoints.Breakpoint.lg)} {
    min-height: 500px;
  }
  width: 100%;

  canvas {
    cursor: move;
  }
`

const Viewer: React.FC<Props> = (props) => {
  const { initPositionZ = 70 } = props

  const viewRef: React.MutableRefObject<skinview3d.SkinViewer> = useRef(null!)
  const containerRef = useRef<HTMLCanvasElement>(null)
  const animationHandlesRef = useRef(animationHandles)

  const [paused, setPaused] = useState(false)
  const [running, setRunning] = useState(false)
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
    const viewer = new skinview3d.FXAASkinViewer({
      canvas: container,
      width: container.clientWidth,
      height: container.clientHeight,
      skin: props.skin || SkinSteve,
      cape: props.cape || '',
      model: props.isAlex ? 'slim' : 'default',
      zoom: initPositionZ / 100,
    })

    if (document.body.classList.contains('dark-mode')) {
      viewer.background = '#6c757d'
    }

    const rotate = viewer.animations.add(skinview3d.RotatingAnimation)
    animationHandlesRef.current.rotate = rotate

    const control = skinview3d.createOrbitControls(viewer)

    viewRef.current = viewer

    return () => {
      control.dispose()
      viewer.dispose()
    }
  }, [])

  useEffect(() => {
    const viewer = viewRef.current
    viewer.loadSkin(props.skin || SkinSteve, props.isAlex ? 'slim' : 'default')
  }, [props.skin, props.isAlex])

  useEffect(() => {
    const viewer = viewRef.current
    if (props.cape) {
      viewer.loadCape(props.cape)
    } else {
      viewer.resetCape()
    }
  }, [props.cape])

  useEffect(() => {
    const handles = animationHandlesRef.current
    if (running) {
      handles.walk?.resetAndRemove()
      handles.walk = null

      const run = viewRef.current.animations.add(skinview3d.RunningAnimation)
      run.speed = 0.6
      handles.run = run
    } else {
      handles.run?.resetAndRemove()
      handles.run = null

      const walk = viewRef.current.animations.add(skinview3d.WalkingAnimation)
      handles.walk = walk
    }
  }, [running])

  useEffect(() => {
    viewRef.current.animations.paused = paused
  }, [paused])

  useEffect(() => {
    viewRef.current.loadBackground(backgrounds[bgPicture]!)
  }, [bgPicture])

  const togglePause = () => {
    setPaused((paused) => !paused)
  }

  const toggleRun = () => {
    setRunning((running) => !running)
  }

  const toggleRotate = () => {
    const handles = animationHandlesRef.current
    if (handles.rotate) {
      handles.rotate.paused = !handles.rotate.paused
    }
  }

  const handleReset = () => {
    const handles = animationHandlesRef.current
    handles.walk?.resetAndRemove()
    handles.run?.resetAndRemove()
    handles.rotate?.resetAndRemove()
    viewRef.current.animations.paused = true
  }

  const setWhite = () => {
    viewRef.current.background = '#fff'
  }
  const setGray = () => {
    viewRef.current.background = '#6c757d'
  }
  const setBlack = () => {
    viewRef.current.background = '#000'
  }
  const setPrevPicture = () => {
    setBgPicture((index) => {
      if (bgPicture <= 0) {
        return PICTURES_COUNT - 1
      } else {
        return index - 1
      }
    })
  }
  const setNextPicture = () => {
    setBgPicture((index) => {
      if (bgPicture >= PICTURES_COUNT - 1) {
        return 0
      } else {
        return index + 1
      }
    })
  }

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
      <div className="card-body p-0">
        <canvas ref={containerRef} css={cssViewer}></canvas>
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
