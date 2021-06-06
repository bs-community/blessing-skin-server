export type User = {
  uid: number
  email: string
  nickname: string
  locale: string | null
  score: number
  avatar: number
  permission: UserPermission
  ip: string
  is_dark_mode: boolean
  last_sign_at: string
  register_at: string
  verified: boolean
}

export const enum UserPermission {
  Banned = -1,
  Normal = 0,
  Admin = 1,
  SuperAdmin = 2,
}

export type Player = {
  pid: number
  name: string
  uid: number
  tid_skin: number
  tid_cape: number
  last_modified: string
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

export const enum TextureType {
  Steve = 'steve',
  Alex = 'alex',
  Cape = 'cape',
}

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
