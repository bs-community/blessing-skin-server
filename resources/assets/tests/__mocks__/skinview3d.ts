/* eslint-disable max-params */
/* eslint-disable max-classes-per-file */
import type { PlayerObject, SkinObject, CapeObject } from 'skinview3d'

export class SkinViewer {
  disposed: boolean

  skinUrl: string

  capeUrl: string

  animationPaused: boolean

  camera: { position: { z: number } }

  playerObject: PlayerObject

  constructor() {
    this.skinUrl = ''
    this.capeUrl = ''
    this.disposed = false
    this.animationPaused = false
    this.camera = {
      position: {
        z: 0,
      },
    }
    this.playerObject = {
      skin: {} as SkinObject,
      cape: {} as CapeObject,
    } as PlayerObject
  }

  dispose() {
    this.disposed = true
  }
}

export class CompositeAnimation {
  add(animation: any) {
    return animation
  }
}

export function createOrbitControls() {}

export const WalkingAnimation = { paused: false }
export const RunningAnimation = { paused: false }
export const RotatingAnimation = { paused: false }

export function isSlimSkin() {
  return false
}
