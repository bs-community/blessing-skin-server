interface I18nTable {
  [key: string]: string | I18nTable | undefined
}

export function t(key: string, parameters = Object.create(null)): string {
  const segments = key.split('.')
  let temp = blessing.i18n as I18nTable | undefined
  let result = ''

  for (const segment of segments) {
    const middle = temp?.[segment]
    if (!middle) {
      return key
    }
    if (typeof middle === 'string') {
      result = middle
    } else {
      temp = middle
    }
  }

  /* eslint-disable @typescript-eslint/no-unsafe-argument */
  Object.keys(parameters).forEach(
    (slot) => (result = result.replace(`:${slot}`, parameters[slot])),
  )
  /* eslint-enable @typescript-eslint/no-unsafe-argument */

  return result
}

Object.assign(window, { trans: t })
Object.assign(blessing, { t })
