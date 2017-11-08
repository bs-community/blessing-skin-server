/* global refreshCaptcha */

'use strict';

$('#forgot-button').click(e => {
    e.preventDefault();

    let data = {
        email: $('#email').val(),
        captcha: $('#captcha').val()
    };

    (function validate({ email, captcha }, callback) {
        if (email == '') {
            showMsg(trans('auth.emptyEmail'));
            $('#email').focus();
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            showMsg(trans('auth.invalidEmail'), 'warning');
        } else if (captcha == '') {
            showMsg(trans('auth.emptyCaptcha'));
            $('#captcha').focus();
        } else {
            callback();
        }
    })(data, async () => {
        try {
            const { errno, msg } = await fetch({
                type: 'POST',
                url: url('auth/forgot'),
                dataType: 'json',
                data: data,
                beforeSend: () => {
                    $('#forgot-button').html(
                        '<i class="fa fa-spinner fa-spin"></i> ' + trans('auth.sending')
                    ).prop('disabled', 'disabled');
                }
            });
            if (errno == 0) {
                showMsg(msg, 'success');
                $('#forgot-button').html(trans('auth.send')).prop('disabled', 'disabled');
            } else {
                showMsg(msg, 'warning');
                refreshCaptcha();
                $('#forgot-button').html(trans('auth.send')).prop('disabled', '');
            }
        } catch (error) {
            showAjaxError(error);
            $('#forgot-button').html(trans('auth.send')).prop('disabled', '');
        }
    });
});
