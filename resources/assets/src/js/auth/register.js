/* global refreshCaptcha */

'use strict';

$('#register-button').click(e => {
    e.preventDefault();

    const data = {
        email: $('#email').val(),
        password: $('#password').val(),
        nickname: $('#nickname').val(),
        captcha: $('#captcha').val(),
    };

    (function validate({ email, password, nickname, captcha }, callback) {
        // Massive form validation
        if (email === '') {
            showMsg(trans('auth.emptyEmail'));
            $('#email').focus();
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            showMsg(trans('auth.invalidEmail'), 'warning');
        } else if (password === '') {
            showMsg(trans('auth.emptyPassword'));
            $('#password').focus();
        } else if (password.length < 8 || password.length > 32) {
            showMsg(trans('auth.invalidPassword'), 'warning');
            $('#password').focus();
        } else if ($('#confirm-pwd').val() === '') {
            showMsg(trans('auth.emptyConfirmPwd'));
            $('#confirm-pwd').focus();
        } else if (password !== $('#confirm-pwd').val()) {
            showMsg(trans('auth.invalidConfirmPwd'), 'warning');
            $('#confirm-pwd').focus();
        } else if (nickname === '') {
            showMsg(trans('auth.emptyNickname'));
            $('#nickname').focus();
        } else if (captcha === '') {
            showMsg(trans('auth.emptyCaptcha'));
            $('#captcha').focus();
        } else {
            callback();
        }

        return;
    })(data, async () => {
        try {
            const { errno, msg } = await fetch({
                type: 'POST',
                url: url('auth/register'),
                dataType: 'json',
                data: data,
                beforeSend: function () {
                    $('#register-button').html(
                        '<i class="fa fa-spinner fa-spin"></i> ' + trans('auth.registering')
                    ).prop('disabled', 'disabled');
                }
            });
            if (errno === 0) {
                swal({ type: 'success', html: msg });

                setTimeout(() => {
                    window.location = url('user');
                }, 1000);
            } else {
                showMsg(msg, 'warning');
                refreshCaptcha();
                $('#register-button').html(trans('auth.register')).prop('disabled', '');
            }
        } catch (error) {
            showAjaxError(error);
            $('#register-button').html(trans('auth.register')).prop('disabled', '');
        }
    });
});
