import type { Texture, User } from '@/scripts/types'

export const enum Status {
  Pending = 0,
  Resolved = 1,
  Rejected = 2,
}

export type Report = {
  id: number
  tid: number
  texture: Texture | null
  uploader: number
  texture_uploader: User | null
  reporter: number
  informer: User | null
  reason: string
  status: Status
  report_at: string
}
