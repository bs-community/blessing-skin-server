$('#send-verification-email').click(async () => {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/email-verification'),
            dataType: 'json',
            beforeSend: () => {
                $('#send-verification-email').hide();
                $('#sending-indicator').show();
            }
        });

        swal({
            type: errno === 0 ? 'success' : 'warning',
            html: msg
        });

    } catch (error) {
        showAjaxError(error);
    }

    $('#send-verification-email').show();
    $('#sending-indicator').hide();
});
