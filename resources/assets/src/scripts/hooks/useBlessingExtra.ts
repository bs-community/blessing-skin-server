import { useState, useEffect } from 'react'

export default function useBlessingExtra<T>(key: string, defaultValue?: T): T {
  const [value, setValue] = useState<T>(defaultValue!)

  useEffect(() => {
    setValue(blessing.extra[key] as T)
  }, [])

  return value
}
