/* eslint-disable max-params */
/* eslint-disable max-classes-per-file */
import type { PlayerObject, SkinObject, CapeObject } from 'skinview3d'

export class FXAASkinViewer {
  disposed = false
  background = ''
  animations = new RootAnimation()
  animationPaused = false

  playerObject: PlayerObject

  constructor() {
    this.animationPaused = false
    this.playerObject = {
      skin: {} as SkinObject,
      cape: {} as CapeObject,
    } as PlayerObject
  }

  loadSkin() {}
  loadCape() {}
  resetCape() {}
  loadBackground() {}

  dispose() {
    this.disposed = true
  }
}

export class RootAnimation {
  paused = false

  add(animation: unknown) {
    return animation
  }
}

export function createOrbitControls() {
  return {
    dispose() {}
  }
}

export const WalkingAnimation = new Proxy({}, {
  get() {
    return jest.fn()
  }
})
export const RunningAnimation = new Proxy({}, {
  get() {
    return jest.fn()
  }
})
export const RotatingAnimation = new Proxy({}, {
  get() {
    return jest.fn()
  }
})

export function isSlimSkin() {
  return false
}
