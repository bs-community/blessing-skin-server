import { loadSkinToCanvas } from 'skinview-utils'

/* istanbul ignore next */
function isTransparent(
  context: CanvasRenderingContext2D,
  x: number,
  y: number,
): boolean {
  const imageData = context.getImageData(x, y, 1, 1)

  return imageData.data[3] !== 255
}

/* istanbul ignore next */
export function isAlex(texture: string): Promise<boolean> {
  return new Promise(resolve => {
    const image = new Image()
    image.src = texture
    image.onload = () => {
      if (image.width !== image.height) {
        resolve(false)
        return
      }

      const canvas = document.createElement('canvas')
      loadSkinToCanvas(canvas, image)

      const ratio = canvas.width / 64
      const context = canvas.getContext('2d')
      if (!context) {
        resolve(false)
        return
      }

      resolve(isTransparent(context, 46 * ratio, 63 * ratio))
    }
  })
}
