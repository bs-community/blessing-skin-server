import { loadSkinToCanvas } from 'skinview-utils'

/* istanbul ignore next */
function checkPixel(
  context: CanvasRenderingContext2D,
  x: number,
  y: number,
): boolean {
  const imageData = context.getImageData(x, y, 1, 1)

  return (
    imageData.data[0] === 0 &&
    imageData.data[1] === 0 &&
    imageData.data[2] === 0
  )
}

/* istanbul ignore next */
export function isAlex(texture: string): Promise<boolean> {
  return new Promise((resolve) => {
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

      for (let x = 46 * ratio; x < 48 * ratio; x += 1) {
        for (let y = 52 * ratio; y < 64 * ratio; y += 1) {
          if (!checkPixel(context, x, y)) {
            resolve(false)
            return
          }
        }
      }

      resolve(true)
    }
  })
}
