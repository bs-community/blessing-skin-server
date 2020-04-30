import { useState, useEffect, useRef } from 'react'
import TWEEN from '@tweenjs/tween.js'

export default function useTween<T = any>(initialValue: T) {
  const [value, setValue] = useState<T>(initialValue)
  const ref = useRef<T>(value)
  const [dest, setDest] = useState<T>(initialValue)

  useEffect(() => {
    function animate() {
      requestAnimationFrame(animate)
      TWEEN.update()
      setValue(ref.current)
    }

    const tween = new TWEEN.Tween(ref)
    tween.to({ current: dest }, 1000).start()
    animate()
  }, [dest])

  return [value, setDest] as const
}
