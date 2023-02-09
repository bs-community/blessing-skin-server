/* eslint-disable max-params */
/* eslint-disable max-classes-per-file */
import type {
  PlayerObject,
  SkinObject,
  CapeObject,
  EarsObject,
} from 'skinview3d'

export class SkinViewer {
  disposed = false
  background = null
  animation = null
  autoRotate = false
  autoRotateSpeed = 1.0

  playerObject: PlayerObject

  constructor() {
    this.playerObject = {
      skin: {} as SkinObject,
      cape: {} as CapeObject,
      ears: {} as EarsObject,
      backEquipment: 'cape',
    } as PlayerObject
  }

  loadSkin() {}
  resetSkin() {}
  loadCape() {}
  resetCape() {}
  loadBackground() {}

  dispose() {
    this.disposed = true
  }
}

export class PlayerAnimation {
  speed = 1.0
  paused = false
  progress = 0
}

export class IdleAnimation extends PlayerAnimation {}

export class WalkingAnimation extends PlayerAnimation {}

export class RunningAnimation extends PlayerAnimation {}

export class FlyingAnimation extends PlayerAnimation {}

export function isSlimSkin() {
  return false
}
