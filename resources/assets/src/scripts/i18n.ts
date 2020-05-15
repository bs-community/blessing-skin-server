export function t(key: string, parameters = Object.create(null)): string {
  const segments = key.split('.')
  let temp = (blessing.i18n) as {
    [k: string]: string | { [k: string]: string }
  }
  let result = ''

  for (const segment of segments) {
    if (!temp[segment]) {
      return key
    }
    const middle = temp[segment]
    if (typeof middle === 'string') {
      result = middle
    } else {
      temp = middle
    }
  }

  Object.keys(parameters)
    .forEach(slot => (result = result.replace(`:${slot}`, parameters[slot])))

  return result
}

Object.assign(window, { trans: t })
