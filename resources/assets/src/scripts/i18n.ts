interface I18nTable {
  [key: string]: string | I18nTable | undefined
}

export function t(key: string, parameters = Object.create(null)): string {
  const segments = key.split('.')
  let temp = blessing.i18n as I18nTable | undefined
  let result = ''

  for (const segment of segments) {
    /* istanbul ignore next */
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

  Object.keys(parameters).forEach(
    (slot) => (result = result.replace(`:${slot}`, parameters[slot])),
  )

  return result
}

Object.assign(window, { trans: t })
Object.assign(blessing, { t })
