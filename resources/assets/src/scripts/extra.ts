export function getExtraData(): Record<string, any> {
  const jsonElement = document.querySelector('#blessing-extra')

  if (jsonElement) {
    return JSON.parse(jsonElement.textContent ?? '{}')
  } else {
    return {}
  }
}

blessing.extra = getExtraData()
