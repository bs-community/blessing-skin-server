/* exported changeUserEmail, changeUserNickName, changeUserPwd,
   changeBanStatus, changeAdminStatus, deleteUserAccount */

'use strict';

function changeUserEmail(uid) {
    let dom = $(`tr#user-${uid} > td:nth-child(2)`),
        newUserEmail = '';

    swal({
        text: trans('admin.newUserEmail'),
        showCancelButton: true,
        input: 'text',
        inputValue: dom.text(),
        inputValidator: value => (new Promise((resolve, reject) => {
            (newUserEmail = value) ? resolve() : reject(trans('auth.emptyEmail'));
        }))
    }).then(email => fetch({
        type: 'POST',
        url: url('admin/users?action=email'),
        dataType: 'json',
        data: { uid: uid, email: email }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            dom.text(newUserEmail);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(err => showAjaxError(err));
}

function changeUserNickName(uid) {
    let dom = $(`tr#user-${uid} > td:nth-child(3)`),
        newNickName = '';

    swal({
        text: trans('admin.newUserNickname'),
        showCancelButton: true,
        input: 'text',
        inputValue: dom.text(),
        inputValidator: value => (new Promise((resolve, reject) => {
            (newNickName = value) ? resolve() : reject(trans('auth.emptyNickname'));
        }))
    }).then(nickname => fetch({
        type: 'POST',
        url: url('admin/users?action=nickname'),
        dataType: 'json',
        data: { uid: uid, nickname: nickname }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            dom.text(newNickName);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(err => showAjaxError(err));
}

function changeUserPwd(uid) {
    swal({
        text: trans('admin.newUserPassword'),
        showCancelButton: true,
        input: 'password',
    }).then(password => fetch({
        type: 'POST',
        url: url('admin/users?action=password'),
        dataType: 'json',
        data: { uid: uid, password: password }
    })).then(({ errno, msg }) => {
        (errno == 0) ? toastr.success(msg) : toastr.warning(msg);
    }).catch(err => showAjaxError(err));
}

function changeUserScore(uid, score) {
    fetch({
        type: 'POST',
        url: url('admin/users?action=score'),
        dataType: 'json',
        // Handle id formatted as '#user-1234'
        data: { uid: uid.slice(5), score: score }
    }).then(({ errno, msg }) => {
        (errno == 0) ? toastr.success(msg) : toastr.warning(msg);
    }).catch(err => showAjaxError(err));
}

function changeBanStatus(uid) {
    fetch({
        type: 'POST',
        url: url('admin/users?action=ban'),
        dataType: 'json',
        data: { uid: uid }
    }).then(({ errno, msg, permission }) => {
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
    }).catch(err => showAjaxError(err));
}

function changeAdminStatus(uid) {
    fetch({
        type: 'POST',
        url: url('admin/users?action=admin'),
        dataType: 'json',
        data: { uid: uid }
    }).then(({ errno, msg, permission }) => {
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
    }).catch(err => showAjaxError(err));
}

function deleteUserAccount(uid) {
    swal({
        text: trans('admin.deleteUserNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => fetch({
        type: 'POST',
        url: url('admin/users?action=delete'),
        dataType: 'json',
        data: { uid: uid }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            $('tr#user-' + uid).remove();
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(err => showAjaxError(err));
}

$('body').on('keypress', '.score', function(event){
    // Change score when Enter key is pressed
    if (event.which == 13) {
        $(this).blur();
        changeUserScore($(this).parent().parent().attr('id'), $(this).val());
    }
});
