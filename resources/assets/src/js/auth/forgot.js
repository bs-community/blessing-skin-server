/* global refreshCaptcha */

'use strict';

$('#forgot-button').click(e => {
    e.preventDefault();

    const data = {
        email: $('#email').val(),
        captcha: $('#captcha').val()
    };

    (function validate({ email, captcha }, callback) {
        if (email === '') {
            showMsg(trans('auth.emptyEmail'));
            $('#email').focus();
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            showMsg(trans('auth.invalidEmail'), 'warning');
        } else if (captcha === '') {
            showMsg(trans('auth.emptyCaptcha'));
            $('#captcha').focus();
        } else {
            callback();
        }
    })(data, async () => {
        try {
            const { errno, msg, remain } = await fetch({
                type: 'POST',
                url: url('auth/forgot'),
                dataType: 'json',
                data: data,
                beforeSend: () => {
                    $('#forgot-button').html(
                        '<i class="fa fa-spinner fa-spin"></i> ' + trans('auth.sending')
                    ).prop('disabled', true);
                }
            });

            if (errno === 0) {
                showMsg(msg, 'success');
                showRemainTimeIndicator(180);
            } else {
                showMsg(msg, 'warning');
                refreshCaptcha();

                if (remain) {
                    showRemainTimeIndicator(remain);
                } else {
                    $('#forgot-button').html(trans('auth.send')).prop('disabled', false);
                }
            }
        } catch (error) {
            showAjaxError(error);
            $('#forgot-button').html(trans('auth.send')).prop('disabled', false);
        }
    });
});

function showRemainTimeIndicator(seconds, intervalID) {
    // Get remain time from elem data if not specified
    if (seconds === undefined) {
        seconds = $('#forgot-button').data('remain');
    }

    if (seconds > 0) {
        $('#forgot-button').html(`${trans('auth.send')} (${seconds})`).prop('disabled', true);
    } else {
        $('#forgot-button').html(trans('auth.send')).prop('disabled', false);
        // Stop timer
        if (intervalID) clearInterval(intervalID);
    }

    // Create timer for decreasing remain time by second
    if (! intervalID) {
        const intervalID = window.setInterval(function () {
            showRemainTimeIndicator(--seconds, intervalID);
        }, 1000);
    }
}

// Start timer
$(document).ready(() => showRemainTimeIndicator());

if (process.env.NODE_ENV === 'test') {
    module.exports = showRemainTimeIndicator;
}
