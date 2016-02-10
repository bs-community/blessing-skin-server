/*
* @Author: prpr
* @Date:   2016-02-03 17:21:46
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-10 20:53:55
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
                        showAlert(json.msg, function(){
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
        showAlert("原密码不能为空");
        $("#passwd").focus();
        return false;
    } else if (new_passwd == ""){
        showAlert("新密码要好好填哦");
        $("#new_passwd").focus();
        return false;
    } else if (confirm_pwd == ""){
        showAlert("确认密码不能为空");
        $("#confirm_pwd").focus();
        return false;
    } else if (new_passwd != confirm_pwd){
        console.log(new_passwd, confirm_pwd)
        showAlert("新密码和确认的密码不一样诶？");
        $("#confirm_pwd").focus();
        return false;
    } else {
        return true;
    }
}

$('#delete').click(function(){
    Ply.dialog("prompt", {
        title: "这是危险操作！输入密码来确认：",
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
                    showAlert(json.msg, function(){
                        window.location = "../index.php";
                    });
                } else {
                    showAlert(json.msg);
                }
            }
        });
    });
});

$('#reset').click(function(){
    Ply.dialog("prompt", {
        title: "这是危险操作！输入密码来确认：",
        form: { passwd: "Password" }
    }).done(function(ui){
        var passwd = ui.data.passwd;
        $.ajax({
            type: "POST",
            url: "../ajax.php?action=reset",
            dataType: "json",
            data: { "uname": docCookies.getItem('uname'), "passwd": passwd },
            success: function(json) {
                if (json.errno == 0) {
                    showAlert(json.msg);
                } else {
                    showAlert(json.msg);
                }
            }
        });
    });
});
