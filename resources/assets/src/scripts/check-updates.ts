// KEEP THIS FILE DEPENDENCIES FREE!

const init: RequestInit = {
  credentials: 'same-origin',
  headers: new Headers({
    Accept: 'application/json',
  }),
}

export async function checkForUpdates(): Promise<void> {
  const response = await fetch(`${blessing.base_url}/admin/update/check`, init)

  if (response.ok) {
    const data = await response.json()
    const el = document.querySelector(`[href="${blessing.base_url}/admin/update"] p`)
    if (data.available && el) {
      el.innerHTML += '<span class="right badge badge-primary">New</span>'
    }
  }
}

export async function checkForPluginUpdates(): Promise<void> {
  const response = await fetch(`${blessing.base_url}/admin/plugins/market/check`, init)

  if (response.ok) {
    const data = await response.json()
    const el = document.querySelector(`[href="${blessing.base_url}/admin/plugins/market"] p`)
    if (data.available && el) {
      const length = data.plugins.length as number
      el.innerHTML += `<span class="right badge badge-success">${length}</span>`
    }
  }
}

// istanbul ignore next
if (process.env.NODE_ENV !== 'test') {
  checkForUpdates()
  checkForPluginUpdates()
}
