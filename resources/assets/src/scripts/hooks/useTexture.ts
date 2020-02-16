import { useState, useEffect } from 'react'
import * as fetch from '../net'
import { Texture, TextureType } from '../types'

type Response = fetch.ResponseBody<Texture>

export default function useTexture(): [
  { url: string; type: TextureType },
  React.Dispatch<React.SetStateAction<number>>,
] {
  const [tid, setTid] = useState(0)
  const [url, setUrl] = useState('')
  const [type, setType] = useState<TextureType>('steve')

  useEffect(() => {
    if (tid <= 0) {
      setUrl('')
      return
    }

    const getTexture = async () => {
      const {
        data: { hash, type },
      } = await fetch.get<Response>(`/skinlib/info/${tid}`)

      setUrl(`${blessing.base_url}/textures/${hash}`)
      setType(type)
    }
    getTexture()
  }, [tid])

  return [{ url, type }, setTid]
}
