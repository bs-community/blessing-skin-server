/** @jsxImportSource @emotion/react */
import React, { useState, useEffect, useRef } from 'react'
import { useMeasure } from 'react-use'
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

const animationFactories = [
  () => new skinview3d.WalkingAnimation(),
  () => new skinview3d.RunningAnimation(),
  () => new skinview3d.FlyingAnimation(),
  () => new skinview3d.IdleAnimation(),
]

const ActionButton = styled.i`
  display: inline;
  padding: 0.5em 0.5em;
  &:hover {
    color: #555;
    cursor: pointer;
  }
`

const cssViewer = css`
  flex: 1 1 auto;
  ${breakpoints.greaterThan(breakpoints.Breakpoint.lg)} {
    min-height: 500px;
  }
  min-height: 300px;
  width: 100%;
  height: 100%;

  canvas {
    display: flex;
    justify-content: center;
  }
`

const Viewer: React.FC<Props> = (props) => {
  const { initPositionZ = 70 } = props

  const viewRef: React.MutableRefObject<skinview3d.SkinViewer> = useRef(null!)
  const containerRef = useRef<HTMLCanvasElement>(null)

  const [paused, setPaused] = useState(false)
  const [animation, setAnimation] = useState(0)
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
      canvas: container,
      width: container.clientWidth,
      height: container.clientHeight,
      skin: props.skin || SkinSteve,
      cape: props.cape || undefined,
      model: props.isAlex ? 'slim' : 'default',
      zoom: initPositionZ / 100,
    })
    viewer.autoRotate = true

    if (document.body.classList.contains('dark-mode')) {
      viewer.background = '#6c757d'
    }

    viewRef.current = viewer

    return () => {
      viewer.dispose()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const [containerWrapperRef, containerMeasure] = useMeasure<HTMLDivElement>()
  useEffect(() => {
    viewRef.current.setSize(containerMeasure.width, containerMeasure.height)
  })

  useEffect(() => {
    const viewer = viewRef.current
    viewer.loadSkin(props.skin || SkinSteve, {
      model: props.isAlex ? 'slim' : 'default',
    })
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
    const viewer = viewRef.current
    const factory = animationFactories[animation]
    if (factory === undefined) {
      viewer.animation = null
    } else {
      const newAnimation = factory()
      newAnimation.paused = paused // Perseve `paused` state
      viewer.animation = newAnimation
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [animation])

  useEffect(() => {
    const currentAnimation = viewRef.current.animation
    if (currentAnimation !== null) {
      currentAnimation.paused = paused
    }
  }, [paused])

  useEffect(() => {
    const viewer = viewRef.current
    const backgroundUrl = backgrounds[bgPicture]
    if (backgroundUrl === undefined) {
      viewer.background = null
    } else {
      viewer.loadBackground(backgroundUrl)
    }
  }, [bgPicture])

  const togglePause = () => {
    setPaused((paused) => {
      if (paused) {
        return false
      } else {
        viewRef.current.autoRotate = false
        return true
      }
    })
  }

  const toggleAnimation = () => {
    setAnimation((index) => (index + 1) % animationFactories.length)
    setPaused(false)
  }

  const toggleRotate = () => {
    const viewer = viewRef.current
    viewer.autoRotate = !viewer.autoRotate
  }

  const toggleBackEquippment = () => {
    const player = viewRef.current.playerObject
    if (player.backEquipment === 'cape') {
      player.backEquipment = 'elytra'
    } else {
      player.backEquipment = 'cape'
    }
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
              className={`fas fa-tablet ${props.cape ? '' : 'd-none'}`}
              data-toggle="tooltip"
              data-placement="bottom"
              title={t('general.switchCapeElytra')}
              onClick={toggleBackEquippment}
            ></ActionButton>
            <ActionButton
              className={`fas fa-person-running`}
              data-toggle="tooltip"
              data-placement="bottom"
              title={t('general.switchAnimation')}
              onClick={toggleAnimation}
            ></ActionButton>
            <ActionButton
              className={`fas fa-${paused ? 'play' : 'pause'}`}
              data-toggle="tooltip"
              data-placement="bottom"
              title={
                paused
                  ? t('general.playAnimation')
                  : t('general.pauseAnimation')
              }
              onClick={togglePause}
            ></ActionButton>
            <ActionButton
              className="fas fa-rotate-right"
              data-toggle="tooltip"
              data-placement="bottom"
              title={t('general.rotation')}
              onClick={toggleRotate}
            ></ActionButton>
          </div>
        </div>
      </div>
      <div ref={containerWrapperRef} css={cssViewer} className="p-0">
        <canvas ref={containerRef}></canvas>
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
