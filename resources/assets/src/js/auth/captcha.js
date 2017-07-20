'use strict';

function refreshCaptcha() {
    let timestamp = new Date().getTime();
    // Refresh Captcha Image
    $('.captcha').attr('src', url(`auth/captcha?${timestamp}`));
    // Clear input
    $('#captcha').val('');
}

$('.captcha').click(refreshCaptcha);

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = refreshCaptcha;
}
