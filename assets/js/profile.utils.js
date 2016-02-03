/*
* @Author: prpr
* @Date:   2016-02-03 17:21:46
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-03 20:25:52
*/

'use strict';

$('#change').click(function(){
    var passwd = $("#passwd").val();
    var new_passwd = $("#new-passwd").val();
    if (checkForm(passwd, new_passwd, $("#confirm-pwd").val())) {
        $.ajax({
            type: "POST",
            url: "../ajax.php?action=change",
            dataType: "json",
            data: {"uname": docCookies.getItem('uname'), "passwd": passwd, "new_passwd": new_passwd},
            success: function(json) {
                if (json.errno == 0) {
                    logout(function(){
                        showAlert(json.msg + " Please log in again.", function(){
                            window.location = "../index.php";
                        });
                    });
                } else {
                    showAlert(json.msg);
                }
            }
        });
    }
});

function checkForm(passwd, new_passwd, confirm_pwd) {
    if (passwd == ""){
        showAlert("Empty Password!");
        //$("#passwd").focus();
        return false;
    } else if (new_passwd == ""){
        showAlert("Empty New Password!");
        $("#new_passwd").focus();
        return false;
    } else if (confirm_pwd == ""){
        showAlert("Empty Confirming Password!");
        $("#confirm_pwd").focus();
        return false;
    } else if (new_passwd != confirm_pwd){
        console.log(new_passwd, confirm_pwd)
        showAlert("Non-equal password confirming!");
        $("#confirm_pwd").focus();
        return false;
    } else {
        return true;
    }
}

$('#delete').click(function(){
    Ply.dialog("prompt", {
        title: "Type in your password to confirm:",
        form: { passwd: "Password" }
    }).done(function(ui){
        var passwd = ui.data.passwd;
        $.ajax({
            type: "POST",
            url: "../ajax.php?action=delete",
            dataType: "json",
            data: { "uname": docCookies.getItem('uname'), "passwd": passwd },
            success: function(json) {
                if (json.errno == 0) {
                    docCookies.removeItem("uname", "/");
                    docCookies.removeItem("token", "/");
                    showAlert(json.msg + " Bye~", function(){
                        window.location = "../index.php";
                    });
                } else {
                    showAlert(json.msg);
                }
            }
        });
    });
});
