'use strict';

async function changeUserEmail(uid) {
    let dom = $(`tr#user-${uid} > td:nth-child(2)`),
        newUserEmail = '';

    try {
        newUserEmail = await swal({
            text: trans('admin.newUserEmail'),
            showCancelButton: true,
            input: 'text',
            inputValue: dom.text(),
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('auth.emptyEmail'));
            }))
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=email'),
            dataType: 'json',
            data: { uid: uid, email: newUserEmail }
        });

        if (errno == 0) {
            dom.text(newUserEmail);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeUserNickName(uid) {
    let dom = $(`tr#user-${uid} > td:nth-child(3)`),
        newNickName = '';

    try {
        newNickName = await swal({
            text: trans('admin.newUserNickname'),
            showCancelButton: true,
            input: 'text',
            inputValue: dom.text(),
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('auth.emptyNickname'));
            }))
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=nickname'),
            dataType: 'json',
            data: { uid: uid, nickname: newNickName }
        });
        if (errno == 0) {
            dom.text(newNickName);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeUserPwd(uid) {
    let password;
    try {
        password = await swal({
            text: trans('admin.newUserPassword'),
            showCancelButton: true,
            input: 'password',
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=password'),
            dataType: 'json',
            data: { uid: uid, password: password }
        });
        errno == 0 ? toastr.success(msg) : toastr.warning(msg);
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeUserScore(uid, score) {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=score'),
            dataType: 'json',
            // Handle id formatted as '#user-1234'
            data: { uid: uid.slice(5), score: score }
        });
        errno == 0 ? toastr.success(msg) : toastr.warning(msg);
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeBanStatus(uid) {
    try {
        const { errno, msg, permission } = await fetch({
            type: 'POST',
            url: url('admin/users?action=ban'),
            dataType: 'json',
            data: { uid: uid }
        });

        if (errno == 0) {
            let dom = $(`#ban-${uid}`);

            if (dom.attr('data') == 'banned') {
                dom.text(trans('admin.ban')).attr('data', 'normal');
            } else {
                dom.text(trans('admin.unban')).attr('data', 'banned');
            }

            $(`#user-${uid} > td.status`).text(
                permission == -1 ? trans('admin.banned') : trans('admin.normal')
            );

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeAdminStatus(uid) {
    try {
        const { errno, msg, permission } = await fetch({
            type: 'POST',
            url: url('admin/users?action=admin'),
            dataType: 'json',
            data: { uid: uid }
        });

        if (errno == 0) {
            let dom = $(`#admin-${uid}`);

            if (dom.attr('data') == 'admin') {
                dom.text(trans('admin.setAdmin')).attr('data', 'normal');
            } else {
                dom.text(trans('admin.unsetAdmin')).attr('data', 'admin');
            }

            $(`#user-${uid} > td.status`).text(
                (permission == 1) ? trans('admin.admin') : trans('admin.normal')
            );

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function deleteUserAccount(uid) {
    try {
        await swal({
            text: trans('admin.deleteUserNotice'),
            type: 'warning',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=delete'),
            dataType: 'json',
            data: { uid: uid }
        });

        if (errno == 0) {
            $('tr#user-' + uid).remove();
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

$('body').on('keypress', '.score', function(event){
    // Change score when Enter key is pressed
    if (event.which == 13) {
        $(this).blur();
        changeUserScore($(this).parent().parent().attr('id'), $(this).val());
    }
});

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        changeUserPwd,
        changeBanStatus,
        changeUserEmail,
        changeUserScore,
        changeAdminStatus,
        deleteUserAccount,
        changeUserNickName,
    };
}
