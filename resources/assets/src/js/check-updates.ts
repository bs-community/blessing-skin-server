import { init } from './net'

export async function checkForUpdates(): Promise<void> {
  const response = await fetch(`${blessing.base_url}/admin/update/check`, init)

  if (response.ok) {
    const data = await response.json()
    const el = document.querySelector(`[href="${blessing.base_url}/admin/update"]`)
    if (data.available && el) {
      el.innerHTML += `
        <span class="pull-right-container">
          <span class="label label-primary pull-right">v${data.latest}</span>
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

Object.assign(window, {
  checkForUpdates,
  checkForPluginUpdates,
})
