'use strict';

$.extend(true, $.fn.dataTable.defaults, {
    language: trans('vendor.datatables'),
    scrollX: true,
    pageLength: 25,
    autoWidth: false,
    processing: true,
    serverSide: true
});

async function sendFeedback() {
    if (document.cookie.replace(/(?:(?:^|.*;\s*)feedback_sent\s*=\s*([^;]*).*$)|^.*$/, '$1') !== '') {
        return;
    }

    try {
        const { errno } = await fetch({
            url: 'https://work.prinzeugen.net/statistics/feedback',
            type: 'POST',
            dataType: 'json',
            data: {
                site_name: blessing.site_name,
                site_url: blessing.base_url,
                version: blessing.version
            }
        });
        if (errno === 0) {
            // It will be expired when current session ends
            document.cookie = 'feedback_sent=' + Date.now();

            console.log('Feedback sent. Thank you!');
        }
    } catch (error) {
        //
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        sendFeedback,
    };
}
