/* global refreshCaptcha */

'use strict';

$('#login-button').click(e => {
    e.preventDefault();
    
    let data = {
        identification: $('#identification').val(),
        password: $('#password').val(),
        keep: $('#keep').prop('checked') ? true : false
    };

    if (data.identification == '') {
        showMsg(trans('auth.emptyIdentification'));
        $('#identification').focus();
    } else if (data.password == '') {
        showMsg(trans('auth.emptyPassword'));
        $('#password').focus();
    } else {
        // Verify it when captcha form is shown
        if ($('#captcha-form').css('display') == 'block') {
            data.captcha = $('#captcha').val();

            if (data.captcha == '') {
                showMsg(trans('auth.emptyCaptcha'));
                $('#captcha').focus();
                return false;
            }
        }

        fetch({
            type: 'POST',
            url: url('auth/login'),
            dataType: 'json',
            data: data,
            beforeSend: () => {
                $('#login-button').html(
                    '<i class="fa fa-spinner fa-spin"></i> ' + trans('auth.loggingIn')
                ).prop('disabled', 'disabled');
            }
        }).then(({ errno, msg, login_fails }) => {
            if (errno == 0) {
                swal({ type: 'success', html: msg });

                window.setTimeout(() => {
                    window.location = url(blessing.redirect_to || 'user');
                }, 1000);
            } else {
                if (login_fails > 3) {
                    if ($('#captcha-form').css('display') == 'none') {
                        swal({ type: 'error', html: trans('auth.tooManyFails') });

                        $('#captcha-form').show();
                    }
                }

                refreshCaptcha();

                showMsg(msg, 'warning');
                $('#login-button').html(trans('auth.login')).prop('disabled', '');
            }
        }).catch(err => {
            showAjaxError(err);
            $('#login-button').html(trans('auth.login')).prop('disabled', '');
        });
    }
});
