/*
* @Author: prpr
* @Date:   2016-02-03 18:23:21
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-04 23:02:46
*/

'use strict';


function showMsg(type, msg) {
    $("[id=msg]").removeClass().addClass("alert").addClass(type).html(msg);
}

function showAlert(msg, callback) {
    callback = callback ? callback : new Function;
    Ply.dialog("alert", msg).done(callback);
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
        showAlert(json.msg + " Successfully logged out.", function(){
            window.location = "../index.php";
        });
    });
});
