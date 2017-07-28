'use strict';

$('#reset-button').click(e => {
    e.preventDefault();
    
    let data = {
        uid: $('#uid').val(),
        password: $('#password').val()
    };

    (function validate({ password }, callback) {
        if (password == '') {
            showMsg(trans('auth.emptyPassword'));
            $('#password').focus();
        } else if (password.length < 8 || password.length > 16) {
            showMsg(trans('auth.invalidPassword'), 'warning');
            $('#password').focus();
        } else if ($('#confirm-pwd').val() == '') {
            showMsg(trans('auth.emptyConfirmPwd'));
            $('#confirm-pwd').focus();
        } else if (password != $('#confirm-pwd').val()) {
            showMsg(trans('auth.invalidConfirmPwd'), 'warning');
            $('#confirm-pwd').focus();
        } else {
            callback();
        }
    })(data, () => {
        fetch({
            type: 'POST',
            url: url('auth/reset'),
            dataType: 'json',
            data: data,
            beforeSend: () => {
                $('#reset-button').html(
                    '<i class="fa fa-spinner fa-spin"></i> ' + trans('auth.resetting')
                ).prop('disabled', 'disabled');
            }
        }).then(({ errno, msg }) => {
            if (errno == 0) {
                swal({
                    type: 'success',
                    html: msg
                }).then(() => (window.location = url('auth/login')));
            } else {
                showMsg(msg, 'warning');
                $('#reset-button').html(trans('auth.reset')).prop('disabled', '');
            }
        }).catch(err => {
            showAjaxError(err);
            $('#reset-button').html(trans('auth.reset')).prop('disabled', '');
        });
    });
});
