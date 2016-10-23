/*
* @Author: printempw
* @Date:   2016-09-15 10:39:41
* @Last Modified by:   printempw
* @Last Modified time: 2016-10-23 11:04:11
*/

'use strict';

// guesss base url
var base_url = (location.pathname.endsWith('user') || location.pathname.endsWith('admin')) ? "." : "..";

function logout(with_out_confirm, callback) {
    if (!with_out_confirm) {
        swal({
            text: trans('general.confirmLogout'),
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: trans('general.confirm'),
            cancelButtonText: trans('general.cancel')
        }).then(function() {
            do_logout(function(json) {
                swal({
                    type: 'success',
                    html: json.msg
                });
                window.setTimeout('window.location = "'+base_url+'/"', 1000);
            });
        });
    } else {
        do_logout(function(json) {
            if (callback) callback(json);
        });
    }
}

function do_logout(callback) {
    $.ajax({
        type: "POST",
        url: base_url + "/auth/logout",
        dataType: "json",
        success: function(json) {
            if (callback) callback(json);
        },
        error: showAjaxError
    });
}
