'use strict';

if ($('#plugin-table').length === 1) {
    $(document).ready(initPluginsTable);
}

function initPluginsTable() {
    $.pluginsTable = $('#plugin-table').DataTable({
        columnDefs: pluginsTableColumnDefs,
        fnDrawCallback: () => $('[data-toggle="tooltip"]').tooltip(),
        rowCallback: (row, data) => $(row).addClass(data.enabled ? 'plugin-enabled' : ''),
        ajax: {
            url: url('admin/plugins/data'),
            type: 'POST'
        }
    }).on('xhr.dt', handleDataTablesAjaxError);
}

const pluginsTableColumnDefs = [
    {
        targets: 0,
        data: 'title',
        title: trans('admin.pluginTitle'),
        width: '10%',
        render: (title, type, row) => {
            const actions = [];

            if (row.enabled) {
                row.config && actions.push(`<a href="${url('admin/plugins/config/'+row.name)}" class="text-primary">${ trans('admin.configurePlugin') }</a>`);
                actions.push(`<a onclick="disablePlugin('${row.name}');" class="text-primary">${ trans('admin.disablePlugin') }</a>`);
            } else {
                actions.push(
                    `<a onclick="enablePlugin('${row.name}');" class="text-primary">${ trans('admin.enablePlugin') }</a>`,
                    `<a onclick="deletePlugin('${row.name}');" class="text-danger">${ trans('admin.deletePlugin') }</a>`
                );
            }

            return `
                <strong>${ title }</strong>
                <div class="actions">${ actions.join(' | ') }</div>
            `;
        }
    },
    {
        targets: 1,
        data: 'description',
        title: trans('admin.pluginDescription'),
        orderable: false,
        render: (description, type, row) => {
            return `
                <div class="plugin-description"><p>${ description }</p></div>
                <div class="plugin-version-author">
                    ${ trans('admin.pluginVersion') } <span class="text-primary">${ row.version }</span> |
                    ${ trans('admin.pluginAuthor') } <a href="${ row.url }">${ row.author }</a> |
                    ${ trans('admin.pluginName') } <span>${ row.name }</span>
                </div>
            `;
        }
    },
    {
        targets: 2,
        data: 'dependencies',
        title: trans('admin.pluginDependencies'),
        searchable: false,
        orderable: false,
        render: data => {
            if (data.requirements.length === 0) {
                return `<i>${trans('admin.noDependencies')}</i>`;
            }

            let result = data.isRequirementsSatisfied ? '' : `<a href="http://t.cn/RrT7SqC" target="_blank" class="label label-primary">${trans('admin.whyDependencies')}</a><br>`;

            for (const name in data.requirements) {
                const constraint = data.requirements[name];
                const color = (name in data.unsatisfiedRequirements) ? 'red' : 'green';

                result += `<span class="label bg-${color}">${name}: ${constraint}</span><br>`;
            }

            return result;
        }
    }
];

async function enablePlugin(name) {
    const dataTable = $.pluginsTable || $.marketTable;

    try {
        const { requirements } = dataTable.row(`#plugin-${name}`).data().dependencies;

        if (requirements.length === 0) {
            await swal({
                text: trans('admin.noDependenciesNotice'),
                type: 'warning',
                showCancelButton: true
            });
        }

        const { errno, msg, reason } = await fetch({
            type: 'POST',
            url: url(`admin/plugins/manage?action=enable&name=${name}`),
            dataType: 'json'
        });

        if (errno === 0) {
            toastr.success(msg);

            dataTable.ajax.reload(null, false);
        } else {
            swal({ type: 'warning', html: `<p>${msg}</p><ul><li>${reason.join('</li><li>')}</li></ul>` });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function disablePlugin(name) {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url(`admin/plugins/manage?action=disable&name=${name}`),
            dataType: 'json'
        });
        if (errno === 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            swal({ type: 'warning', html: msg });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function deletePlugin(name) {
    try {
        await swal({
            text: trans('admin.confirmDeletion'),
            type: 'warning',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url(`admin/plugins/manage?action=delete&name=${name}`),
            dataType: 'json'
        });
        if (errno === 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            swal({ type: 'warning', html: msg });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        initPluginsTable,
        deletePlugin,
        enablePlugin,
        disablePlugin,
    };
}
