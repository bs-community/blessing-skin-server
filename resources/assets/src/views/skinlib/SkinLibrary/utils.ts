import { t } from '@/scripts/i18n'
import { Filter } from './types'

export function humanizeType(type: Filter): string {
  switch (type) {
    case 'steve':
      return 'Steve'
    case 'alex':
      return 'Alex'
    default:
      return t(`general.${type}`)
  }
}
