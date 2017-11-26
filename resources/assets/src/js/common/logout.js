'use strict';

async function confirmLogout() {
    try {
        await swal({
            text: trans('general.confirmLogout'),
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: trans('general.confirm'),
            cancelButtonText: trans('general.cancel')
        });
    } catch (error) {
        return;
    }

    try {
        const { msg } = await logout();
        setTimeout(() => window.location = url(), 1000);
        swal({
            type: 'success',
            html: msg
        });
    } catch (error) {
        showAjaxError(error);
    }
}

function logout() {
    return fetch({
        type: 'POST',
        url: url('auth/logout'),
        dataType: 'json'
    });
}

$('#logout-button').click(confirmLogout);
