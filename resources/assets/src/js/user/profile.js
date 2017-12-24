'use strict';

async function changeNickName() {
    let name = $('#new-nickname').val();

    if (! name) {
        return swal({ type: 'error', html: trans('user.emptyNewNickName') });
    }

    try {
        await swal({
            text: trans('user.changeNickName', { new_nickname: name }),
            type: 'question',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/profile?action=nickname'),
            dataType: 'json',
            data: { new_nickname: name }
        });

        if (errno == 0) {

            $('.nickname').each(function () {
                $(this).html(name);
            });

            return swal({ type: 'success', html: msg });
        } else {
            return swal({ type: 'warning', html: msg });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changePassword() {
    let $oldPasswd = $('#password'),
        $newPasswd = $('#new-passwd'),
        $confirmPwd = $('#confirm-pwd');

    let password = $oldPasswd.val(),
        newPasswd = $newPasswd.val();

    if (password == '') {
        toastr.info(trans('user.emptyPassword'));
        $oldPasswd.focus();
    } else if (newPasswd == '') {
        toastr.info(trans('user.emptyNewPassword'));
        $newPasswd.focus();
    } else if ($confirmPwd.val() == '') {
        toastr.info(trans('auth.emptyConfirmPwd'));
        $confirmPwd.focus();
    } else if (newPasswd != $confirmPwd.val()) {
        toastr.warning(trans('auth.invalidConfirmPwd'));
        $confirmPwd.focus();
    } else {
        try {
            const { errno, msg } = await fetch({
                type: 'POST',
                url: url('user/profile?action=password'),
                dataType: 'json',
                data: { 'current_password': password, 'new_password': newPasswd }
            });

            if (errno == 0) {
                try {
                    await swal({
                        type: 'success',
                        text: msg
                    });
                    await logout();
                } catch (error) {
                    docCookies.removeItem('token') && console.warn(error);
                } finally {
                    window.location = url('auth/login');
                }
                return;
            } else {
                return swal({ type: 'warning', text: msg });
            }
        } catch (error) {
            showAjaxError(error);
        }
    }
}

$('#new-email').focusin(() => {
    $('#current-password').parent().show();
}).focusout(debounce(() => {
    let dom = $('#current-password');

    if (! dom.is(':focus')) {
        dom.parent().hide();
    }
}, 10));

async function changeEmail() {
    const newEmail = $('#new-email').val();

    if (! newEmail) {
        return swal({ type: 'error', html: trans('user.emptyNewEmail') });
    }

    // check valid email address
    if (!/\S+@\S+\.\S+/.test(newEmail)) {
        return swal({ type: 'warning', html: trans('auth.invalidEmail') });
    }

    try {
        await swal({
            text: trans('user.changeEmail', { new_email: newEmail }),
            type: 'question',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/profile?action=email'),
            dataType: 'json',
            data: { new_email: newEmail, password: $('#current-password').val() }
        });

        if (errno == 0) {
            await swal({
                type: 'success',
                text: msg
            });
            
            try {
                await logout();
            } catch (error) {
                docCookies.removeItem('token') && console.warn(error);
            } finally {
                window.location = url('auth/login');
            }
        } else {
            return swal({ type: 'warning', text: msg });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function deleteAccount() {
    let password = $('.modal-body>#password').val();

    if (! password) {
        return swal({ type: 'warning', html: trans('user.emptyDeletePassword') });
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/profile?action=delete'),
            dataType: 'json',
            data: { password: password }
        });

        if (errno == 0) {
            await swal({
                type: 'success',
                html: msg
            });
            window.location = url('auth/login');
        } else {
            return swal({ type: 'warning', html: msg });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        changeEmail,
        deleteAccount,
        changeNickName,
        changePassword,
    };
}
