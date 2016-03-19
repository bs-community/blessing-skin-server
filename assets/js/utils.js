/*
* @Author: printempw
* @Date:   2016-02-03 18:23:21
* @Last Modified by:   printempw
* @Last Modified time: 2016-03-19 10:08:58
*/

'use strict';

function showMsg(type, msg) {
    $("[id=msg]").removeClass().addClass("alert").addClass(type).html(msg);
}

function showCallout(type, msg) {
    $("[id=msg]").removeClass().addClass("callout").addClass(type).html(msg);
}

function showAlert(msg, callback, callback2) {
    callback = callback ? callback : new Function;
    Ply.dialog("alert", msg).done(callback).fail(callback2);
}

function isMobile() {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}

function logout(callback) {
    $.ajax({
        type: "POST",
        url: "../ajax.php?action=logout",
        dataType: "json",
        data: {"uname": docCookies.getItem('uname')},
        success: function(json) {
            docCookies.removeItem("uname", "/");
            docCookies.removeItem("token", "/");
            callback(json);
        }
    });
}

$("#logout").click(function(){
    logout(function(json){
        showAlert(json.msg, function(){
            window.location = "../index.php";
        });
    });
});
