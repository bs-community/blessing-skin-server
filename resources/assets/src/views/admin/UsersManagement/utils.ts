import { t } from '@/scripts/i18n'
import { type User, UserPermission } from '@/scripts/types'

export function humanizePermission(permission: UserPermission): string {
  switch (permission) {
    case UserPermission.Banned:
      return t('admin.banned')
    case UserPermission.Normal:
      return t('admin.normal')
    case UserPermission.Admin:
      return t('admin.admin')
    case UserPermission.SuperAdmin:
      return t('admin.superAdmin')
  }
}

export function verificationStatusText(isVerified: boolean): string {
  return isVerified ? t('admin.verified') : t('admin.unverified')
}

export function canModifyUser(target: User, current: User): boolean {
  return target.uid === current.uid || current.permission > target.permission
}

export function canModifyPermission(target: User, current: User): boolean {
  return current.permission > target.permission
}
