import type { TextureType } from '@/scripts/types'

export type Filter = 'skin' | TextureType

export type LibraryItem = {
  tid: number
  name: string
  type: TextureType
  uploader: number
  public: boolean
  likes: number
  nickname: string
}
