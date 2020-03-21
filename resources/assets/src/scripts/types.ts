export type User = {
  uid: number
  email: string
  nickname: string
  score: number
  avatar: number
  permission: number
  ip: string
  last_sign_at: string
  register_at: string
  verified: boolean
}

export type Player = {
  pid: number
  name: string
  tid_skin: number
  tid_cape: number
}

export type Texture = {
  tid: number
  name: string
  type: TextureType
  hash: string
  size: number
  uploader: number
  public: boolean
  upload_at: string
  likes: number
}

export type TextureType = 'steve' | 'alex' | 'cape'

export type ClosetItem = Texture & {
  pivot: { user_uid: number; texture_tid: number; item_name: string }
}

export type Paginator<T> = {
  data: T[]
  current_page: number
  last_page: number
  from: number
  to: number
  total: number
}
