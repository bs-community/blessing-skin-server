/* eslint-disable max-classes-per-file */

export class SkinViewer {
  constructor() {
    this.animationPaused = false
    this.camera = {
      position: {},
    }
  }

  dispose() {
    this.disposed = true
  }
}

export class CompositeAnimation {
  add(animation) {
    return animation
  }
}

export function createOrbitControls() {}

export const WalkingAnimation = { paused: false }
export const RunningAnimation = { paused: false }
export const RotatingAnimation = { paused: false }
