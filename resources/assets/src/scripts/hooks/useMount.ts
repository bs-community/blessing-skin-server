import { useEffect, useMemo } from 'react'

export default function useMount(selector: string): HTMLElement {
  const container = useMemo(() => document.createElement('div'), [])

  useEffect(() => {
    const mount = document.querySelector(selector)!
    mount.appendChild(container)

    return () => {
      mount.removeChild(container)
    }
  }, [])

  return container
}
