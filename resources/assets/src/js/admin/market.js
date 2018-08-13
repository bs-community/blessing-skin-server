'use strict';

if ($('#market-table').length === 1) {
    $(document).ready(initMarketTable);
}

function initMarketTable() {
    $.marketTable = $('#market-table').DataTable({
        columnDefs: marketTableColumnDefs,
        ajax: {
            url: url('admin/plugins/market-data'),
            type: 'POST'
        }
    }).on('xhr.dt', handleDataTablesAjaxError);
}

const marketTableColumnDefs = [
    {
        targets: 0,
        title: trans('admin.pluginTitle'),
        data: 'title',
        render: (title, type, row) => `<strong>${ title }</strong><div class="plugin-name">${ row.name }</div>`
    },
    {
        targets: 1,
        title: trans('admin.pluginDescription'),
        data: 'description',
        orderable: false
    },
    {
        targets: 2,
        title: trans('admin.pluginAuthor'),
        data: 'author',
        orderable: false
    },
    {
        targets: 3,
        title: trans('admin.pluginVersion'),
        data: 'version',
        orderable: false
    },
    {
        targets: 4,
        data: 'dependencies',
        title: trans('admin.pluginDependencies'),
        searchable: false,
        orderable: false,
        render: data => {
            if (data.requirements.length === 0) {
                return `<i>${trans('admin.noDependencies')}</i>`;
            }

            let result = '';

            for (const name in data.requirements) {
                const constraint = data.requirements[name];
                const color = (name in data.unsatisfiedRequirements) ? 'red' : 'green';

                result += `<span class="label bg-${color}">${name}: ${constraint}</span><br>`;
            }

            return result;
        }
    },
    {
        targets: 5,
        title: trans('admin.pluginOperations'),
        orderable: false,
        render: (data, type, row) => {
            if (row.installed) {
                if (row.update_available) {
                    return `
                        <button class="btn btn-success btn-sm" onclick="updatePlugin('${row.name}');">
                            <i class="fa fa-refresh" aria-hidden="true"></i> ${ trans('admin.updatePlugin') }
                        </button>
                    `;
                }
                if (row.enabled) {
                    return `
                        <button class="btn btn-primary btn-sm" disabled>
                            ${ trans('admin.pluginEnabled') }
                        </button>
                    `;
                }
                return `
                    <button class="btn btn-primary btn-sm" onclick="enablePlugin('${row.name}');">
                        <i class="fa fa-plug" aria-hidden="true"></i> ${ trans('admin.enablePlugin') }
                    </button>
                `;
            }
            return `
                <button class="btn btn-default btn-sm" onclick="installPlugin('${row.name}');">
                    <i class="fa fa-download" aria-hidden="true"></i> ${ trans('admin.installPlugin') }
                </button>
            `;
        }
    }
];

async function installPlugin(name, option = {}) {
    const button = $(`#plugin-${name} .btn`);
    const originalBtnText = button.html();

    try {
        const { errno, msg } = await fetch($.extend(true, {
            type: 'POST',
            url: url('admin/plugins/market/download'),
            dataType: 'json',
            data: { name },
            beforeSend: () => {
                button.html(`<i class="fa fa-spinner fa-spin"></i> ${ trans('admin.pluginInstalling') }`).prop('disabled', true);
            }
        }, option));

        if (errno === 0) {
            toastr.success(msg);

            $.marketTable.ajax.reload(null, false);
        } else {
            button.html(originalBtnText).prop('disabled', false);
            swal({ type: 'warning', html: msg });
        }
    } catch (error) {
        button.html(originalBtnText).prop('disabled', false);
        showAjaxError(error);
    }
}

async function updatePlugin(name) {
    const data = $.marketTable.row(`#plugin-${name}`).data();

    if (data.installed === false) {
        return swal({ type: 'warning', html: 'not installed' });
    }

    try {
        await swal({
            text: trans('admin.confirmUpdate', { plugin: data.title, old: data.installed, new: data.version }),
            type: 'warning',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    installPlugin(name, {
        beforeSend: () => {
            $(`#plugin-${name} .btn`).html(`<i class="fa fa-refresh fa-spin"></i> ${ trans('admin.pluginUpdating') }`).prop('disabled', true);
        }
    });
}

async function checkForPluginUpdates() {
    try {
        const data = await fetch({ url: url('admin/plugins/market/check') });
        if (data.available === true) {
            const dom = `<span class="label label-success pull-right">${data.plugins.length}</span>`;

            $(`[href="${url('admin/plugins/market')}"]`).append(dom);
        }
    } catch (error) {
        //
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        checkForPluginUpdates,
        initMarketTable,
        installPlugin,
        updatePlugin,
    };
}
