'use strict';

async function enablePlugin(name) {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url(`admin/plugins/manage?action=enable&name=${name}`),
            dataType: 'json'
        });
        if (errno == 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            toastr.warning(msg);
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
        if (errno == 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            toastr.warning(msg);
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
        if (errno == 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        deletePlugin,
        enablePlugin,
        disablePlugin,
    };
}
