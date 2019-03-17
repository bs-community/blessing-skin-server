import { init } from './net'

export async function checkForUpdates() {
  const response = await fetch(`${blessing.base_url}/admin/update/check`, init)

  if (response.ok) {
    const data = await response.json()
    if (data.available) {
      document.querySelector(`[href="${blessing.base_url}/admin/update"]`)
        .innerHTML += `
        <span class="pull-right-container">
          <span class="label label-primary pull-right">v${data.latest}</span>
        </span>`
    }
  }
}

export async function checkForPluginUpdates() {
  const response = await fetch(`${blessing.base_url}/admin/plugins/market/check`, init)

  if (response.ok) {
    const data = await response.json()
    if (data.available) {
      document.querySelector(`[href="${blessing.base_url}/admin/plugins/market"]`)
        .innerHTML += `
        <span class="pull-right-container">
          <span class="label label-success pull-right">${data.plugins.length}</span>
        </span>`
    }
  }
}

window.checkForUpdates = checkForUpdates
window.checkForPluginUpdates = checkForPluginUpdates
