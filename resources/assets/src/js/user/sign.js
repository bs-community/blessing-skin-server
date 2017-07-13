/* exported signIn */

function signIn() {
    fetch({
        type: 'POST',
        url: url('user/sign-in'),
        dataType: 'json'
    }).then(result => {
        if (result.errno == 0) {
            $('#score').html(result.score);
            var dom = '<i class="fa fa-calendar-check-o"></i> &nbsp;' + trans('user.signInRemainingTime', { time: String(result.remaining_time) });
            $('#sign-in-button').attr('disabled', 'disabled').html(dom);

            if (result.storage.used > 1024) {
                $('#user-storage').html(`<b>${Math.round(result.storage.used)}</b>/ ${Math.round(result.storage.total)} MB`);
            } else {
                $('#user-storage').html(`<b>${Math.round(result.storage.used)}</b>/ ${Math.round(result.storage.total)} KB`);
            }

            $('#user-storage-bar').css('width', `${result.storage.percentage}%`);

            return swal({ type: 'success', html: result.msg });
        } else {
            toastr.warning(result.msg);
        }
    }).catch(err => showAjaxError(err));
}
