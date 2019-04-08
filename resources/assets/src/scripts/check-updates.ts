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
    const el = document.querySelector(`[href="${blessing.base_url}/admin/update"]`)
    if (data.available && el) {
      el.innerHTML += `
        <span class="pull-right-container">
          <span class="label label-primary pull-right">1</span>
        </span>`
    }
  }
}

export async function checkForPluginUpdates(): Promise<void> {
  const response = await fetch(`${blessing.base_url}/admin/plugins/market/check`, init)

  if (response.ok) {
    const data = await response.json()
    const el = document.querySelector(`[href="${blessing.base_url}/admin/plugins/market"]`)
    if (data.available && el) {
      el.innerHTML += `
        <span class="pull-right-container">
          <span class="label label-success pull-right">${data.plugins.length}</span>
        </span>`
    }
  }
}

// istanbul ignore next
if (process.env.NODE_ENV !== 'test') {
  checkForUpdates()
  checkForPluginUpdates()
}
