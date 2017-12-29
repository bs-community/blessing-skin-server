'use strict';

function refreshCaptcha() {
    const timestamp = new Date().getTime();
    // Refresh Captcha Image
    $('.captcha').attr('src', url(`auth/captcha?${timestamp}`));
    // Clear input
    $('#captcha').val('');
}

$('.captcha').click(refreshCaptcha);

if (process.env.NODE_ENV === 'test') {
    module.exports = refreshCaptcha;
}
