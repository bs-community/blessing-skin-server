import { trans } from '../../../scripts/i18n'

const ONE_MINUTE = 60 * 1000
const ONE_HOUR = 60 * ONE_MINUTE
const ONE_DAY = 24 * ONE_HOUR

export function remainingTime(
  lastSign: Date,
  signGap: number,
  canSignAfterZero: boolean,
): number {
  if (canSignAfterZero) {
    const today = new Date().setHours(0, 0, 0, 0)
    const tomorrow = today + ONE_DAY
    const rest = tomorrow - Date.now()

    return lastSign.valueOf() < today ? 0 : rest
  }

  return lastSign.valueOf() + signGap * ONE_HOUR - Date.now()
}

export function remainingTimeText(remainingTime: number): string {
  const time = remainingTime / ONE_MINUTE
  return time < 60
    ? trans('user.sign-remain-time', {
        time: ~~time,
        unit: trans('user.time-unit-min'),
      })
    : trans('user.sign-remain-time', {
        time: ~~(time / 60),
        unit: trans('user.time-unit-hour'),
      })
}
