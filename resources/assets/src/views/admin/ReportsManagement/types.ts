import { Texture } from '@/scripts/types'

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
  uploaderName: string
  reporter: number
  reporterName: string
  reason: string
  status: Status
  report_at: string
}
