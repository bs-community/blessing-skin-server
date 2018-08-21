import { init } from './net';

async function checkForUpdates() {
    const response = await fetch(`${blessing.base_url}/admin/update/check`, init);

    if (response.ok) {
        const data = await response.json();
        if (data.available) {
            const dom = `<span class="pull-right-container"><span class="label label-primary pull-right">v${data.latest}</span></span>`;

            $(`[href="${blessing.base_url}/admin/update"]`).append(dom);
        }
    }
}

async function checkForPluginUpdates() {
    const response = await fetch(`${blessing.base_url}/admin/plugins/market/check`, init);

    if (response.ok) {
        const data = await response.json();
        if (data.available) {
            const dom = `<span class="pull-right-container"><span class="label label-success pull-right">${data.plugins.length}</span></span>`;

            $(`[href="${blessing.base_url}/admin/plugins/market"]`).append(dom);
        }
    }
}

window.checkForUpdates = checkForUpdates;
window.checkForPluginUpdates = checkForPluginUpdates;
