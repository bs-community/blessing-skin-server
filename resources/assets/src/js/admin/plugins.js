'use strict';

function enablePlugin(name) {
    fetch({
        type: 'POST',
        url: url(`admin/plugins/manage?action=enable&name=${name}`),
        dataType: 'json'
    }).then(({ errno, msg }) => {
        if (errno == 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function disablePlugin(name) {
    fetch({
        type: 'POST',
        url: url(`admin/plugins/manage?action=disable&name=${name}`),
        dataType: 'json'
    }).then(({ errno, msg }) => {
        if (errno == 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function deletePlugin(name) {
    swal({
        text: trans('admin.confirmDeletion'),
        type: 'warning',
        showCancelButton: true
    }).then(() => fetch({
        type: 'POST',
        url: url(`admin/plugins/manage?action=delete&name=${name}`),
        dataType: 'json'
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            toastr.success(msg);

            $.pluginsTable.ajax.reload(null, false);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        enablePlugin,
        disablePlugin,
        deletePlugin
    };
}
