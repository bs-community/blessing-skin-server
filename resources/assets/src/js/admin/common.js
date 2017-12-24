/* global initUsersTable, initPlayersTable, initPluginsTable */

'use strict';

$.pluginsTable = null;

$(document).ready(initTables);

function initTables() {
    $.extend(true, $.fn.dataTable.defaults, {
        language: trans('vendor.datatables'),
        scrollX: true,
        pageLength: 25,
        autoWidth: false,
        processing: true,
        serverSide: true
    });

    if ($('#user-table').length === 1) {
        initUsersTable();
    } else if ($('#player-table').length === 1) {
        initPlayersTable();
    } else if ($('#plugin-table').length === 1) {
        $.pluginsTable = initPluginsTable();
    }
}

async function sendFeedback() {
    if (docCookies.getItem('feedback_sent') !== null)
        return;

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
            // Will be expired when current session ends
            docCookies.setItem('feedback_sent', Date.now());

            console.log('Feedback sent. Thank you!');
        }
    } catch (error) {
        //
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        sendFeedback,
        initTables
    };
}
