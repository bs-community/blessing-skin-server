function handler(event: Event): void {
  document.body.className =
    document.body.className.replace(
      /skin-\w+(?:-light)?/,
      // eslint-disable-next-line no-extra-parens
      (event.target as HTMLInputElement).value,
    )
}

const table = document.querySelector('#change-color')
/* istanbul ignore next */
if (table) {
  table.addEventListener('change', handler)
}

export default handler
