/*
 * @Author: printempw
 * @Date:   2016-07-16 09:02:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-06 18:52:23
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
    $("[id=msg]").removeClass().addClass("alert").addClass('alert-'+type).html(msg);
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
        if (!window.confirm('确定要登出吗？')) return;
    }

    $.ajax({
        type: "POST",
        url: "../auth/logout",
        dataType: "json",
        success: function(json) {
            docCookies.removeItem("email", "/");
            docCookies.removeItem("token", "/");
            // silent
            if (!with_out_confirm) {
                toastr.success(json.msg);
                window.setTimeout('window.location = "../"', 1000);
            } else {
                if (callback) callback(json);
            }
        }
    });
}

/**
 * cookie.js
 * https://developer.mozilla.org/en-US/docs/DOM/document.cookie
 */
var docCookies = {
    getItem: function (sKey) {
        if (!sKey) { return null; }
        return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
    },
    setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
        if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
        var sExpires = "";
        if (vEnd) {
            switch (vEnd.constructor) {
                case Number:
                    sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
                    break;
                case String:
                    sExpires = "; expires=" + vEnd;
                    break;
                case Date:
                    sExpires = "; expires=" + vEnd.toUTCString();
                    break;
            }
        }
        document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
        return true;
    },
    removeItem: function (sKey, sPath, sDomain) {
        if (!this.hasItem(sKey)) { return false; }
        document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "");
        return true;
    },
    hasItem: function (sKey) {
        if (!sKey) { return false; }
        return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
    },
    keys: function () {
        var aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
        for (var nLen = aKeys.length, nIdx = 0; nIdx < nLen; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
        return aKeys;
    }
};

