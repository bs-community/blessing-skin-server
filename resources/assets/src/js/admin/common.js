'use strict';

$.extend(true, $.fn.dataTable.defaults, {
    language: trans('vendor.datatables'),
    scrollX: true,
    pageLength: 25,
    autoWidth: false,
    processing: true,
    serverSide: true
});

$.fn.dataTable.ext.errMode = 'none';

function handleDataTablesAjaxError(event, settings, json, xhr) {
    if (json === null) {
        showModal(xhr.responseText, trans('general.fatalError'), 'danger');
    }
}

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
            },
            xhr: () => {
                // Don't send 'X-CSRF-TOKEN' header to a cross-origin server
                // @see https://gist.github.com/7kfpun/a8d1326db44aa7857660
                const xhr = $.ajaxSettings.xhr();
                const setRequestHeader = xhr.setRequestHeader;
                xhr.setRequestHeader = function (name, value) {
                    if (name === 'X-CSRF-TOKEN') return;
                    setRequestHeader.call(this, name, value);
                };
                return xhr;
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
        handleDataTablesAjaxError,
    };
}
