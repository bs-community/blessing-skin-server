/* global initUsersTable, initPlayersTable, initPluginsTable */

'use strict';

$.pluginsTable = null;

$(document).ready(() => {
    $.extend(true, $.fn.dataTable.defaults, {
        language: trans('vendor.datatables'),
        scrollX: true,
        pageLength: 25,
        autoWidth: false,
        processing: true,
        serverSide: true
    });

    if (window.location.pathname.includes('admin/users')) {
        initUsersTable();
    } else if (window.location.pathname.includes('admin/players')) {
        initPlayersTable();
    } else if (window.location.pathname.includes('admin/plugins/manage')) {
        $.pluginsTable = initPluginsTable();
    }
});
