export type Player = {
  pid: number
  name: string
  tid_skin: number
  tid_cape: number
}

export type Texture = {
  tid: number
  hash: string
  type: TextureType
}

export type TextureType = 'steve' | 'alex' | 'cape'
