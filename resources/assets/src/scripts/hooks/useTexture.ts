import { useState, useEffect } from 'react'
import * as fetch from '../net'
import { type Texture, TextureType } from '../types'

export default function useTexture() {
  const [tid, setTid] = useState(0)
  const [url, setUrl] = useState('')
  const [type, setType] = useState(TextureType.Steve)

  useEffect(() => {
    if (tid <= 0) {
      setUrl('')
      return
    }

    const getTexture = async () => {
      const { hash, type } = await fetch.get<Texture>(`/skinlib/info/${tid}`)

      setUrl(`${blessing.base_url}/textures/${hash}`)
      setType(type)
    }
    getTexture()
  }, [tid])

  return [{ url, type }, setTid] as const
}
