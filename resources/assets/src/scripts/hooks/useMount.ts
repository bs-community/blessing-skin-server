import { useEffect, useRef } from 'react'

export default function useMount(selector: string): HTMLElement | null {
  const container = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    const mount = document.querySelector(selector)!
    const div = document.createElement('div')
    container.current = div

    mount.appendChild(div)

    return () => {
      mount.removeChild(div)
      container.current = null
    }
  }, [])

  return container.current
}
