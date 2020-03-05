import { useState, useEffect } from 'react'

export default function useBlessingExtra<T>(key: string): T {
  const [value, setValue] = useState<T>({} as T)

  useEffect(() => {
    setValue(blessing.extra[key] as T)
  }, [])

  return value
}
