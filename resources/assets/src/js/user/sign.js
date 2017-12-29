async function sign() {
    try {
        const result = await fetch({
            type: 'POST',
            url: url('user/sign'),
            dataType: 'json'
        });

        if (result.errno === 0) {
            $('#score').html(result.score);
            const dom = '<i class="fa fa-calendar-check-o"></i> &nbsp;' + trans(
                'user.signRemainingTime',
                result.remaining_time >= 1
                    ? { time: result.remaining_time.toString(), unit: trans('user.timeUnitHour') }
                    : { time: (result.remaining_time * 60).toFixed(), unit: trans('user.timeUnitMin') }
            );

            $('#sign-button').attr('disabled', 'disabled').html(dom);

            if (result.storage.used > 1024) {
                $('#user-storage').html(
                    `<b>${Math.round(result.storage.used / 1024)}</b>/ ${Math.round(result.storage.total / 1024)} MB`
                );
            } else {
                $('#user-storage').html(`<b>${Math.round(result.storage.used)}</b>/ ${Math.round(result.storage.total)} KB`);
            }

            $('#user-storage-bar').css('width', `${result.storage.percentage}%`);

            return swal({ type: 'success', html: result.msg });
        } else {
            toastr.warning(result.msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = sign;
}
