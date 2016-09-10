/*
 * @Author: printempw
 * @Date:   2016-07-16 09:02:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-09-10 17:10:08
 */

function showModal(msg, title, type, callback) {
    title = title === undefined ? "Messgae" : title;
    type  = type  === undefined ? "default" : type;
    callback = callback === undefined ? 'data-dismiss="modal"' : 'onclick="'+callback+'"';
    var btn_type = (type != "default") ? "btn-outline" : "btn-primary";
    var dom = '<div class="modal modal-'+type+' fade in"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button><h4 class="modal-title">'+title+'</h4></div><div class="modal-body"><p>'+msg+'</p></div><div class="modal-footer"><button type="button" '+callback+' class="btn '+btn_type+'">OK</button></div></div></div></div>';
    $(dom).modal();
}

function showMsg(msg, type) {
    type  = type  === undefined ? "info" : type;
    $("[id=msg]").removeClass().addClass("callout").addClass('callout-'+type).html(msg);
}

function showAjaxError(json) {
    showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
}

function isMobile() {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}

function getQueryString(key) {
    result = location.search.match(new RegExp('[\?\&]'+key+'=([^\&]+)','i'));

    if (result == null || result.length < 1){
        return "";
    } else {
        return result[1];
    }
}

function logout(with_out_confirm, callback) {
    if (!with_out_confirm) {
        swal({
            text: '确定要登出吗？',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: '确定',
            cancelButtonText: '取消'
        }).then(function() {
            do_logout(function(json) {
                swal({
                    type: 'success',
                    html: json.msg
                });
                window.setTimeout('window.location = "../"', 1000);
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
        url: "../auth/logout",
        dataType: "json",
        success: function(json) {
            if (callback) callback(json);
        },
        error: showAjaxError
    });
}
