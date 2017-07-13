'use strict';

function confirmLogout() {
    swal({
        text: trans('general.confirmLogout'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    }).then(() => {
        logout().then(json => {
            swal({
                type: 'success',
                html: json.msg
            });
            window.setTimeout(() => window.location = url(), 1000);
        });
    });
}

function logout() {
    return fetch({
        type: 'POST',
        url: url('auth/logout'),
        dataType: 'json'
    });
}

$('#logout-button').click(() => confirmLogout());
