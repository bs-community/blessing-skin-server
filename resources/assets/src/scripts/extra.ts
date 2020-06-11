export function getExtraData(): any {
  const jsonElement = document.querySelector('#blessing-extra')
  /* istanbul ignore next */
  if (jsonElement) {
    return JSON.parse(jsonElement.textContent ?? '{}')
  }
}

const extraData = getExtraData()
if (extraData) {
  blessing.extra = extraData
}
