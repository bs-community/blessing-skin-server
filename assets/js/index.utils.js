/*
* @Author: prpr
* @Date:   2016-01-21 13:55:44
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-05 21:11:56
*/

'use strict';

var login = function() {
    var uname = $("#uname").val();
    var passwd = $("#passwd").val();
    if (checkForm("login", uname, passwd)) {
        $.ajax({
            type: "POST",
            url: "ajax.php?action=login",
            dataType: "json",
            data: { "uname": uname, "passwd": passwd },
            beforeSend: function() {
                showMsg('alert-info', '登录中。。');
            },
            success: function(json) {
                if (json.errno == 0) {
                    docCookies.setItem('uname', uname, null, '/');
                    docCookies.setItem('token', json.token, null, '/');
                    if ($('#keep').prop('checked')) {
                        docCookies.setItem('uname', uname, 604800, '/');
                        // 设置长效 token （7天）
                        docCookies.setItem('token', json.token, 604800, '/');
                    }
                    showAlert(json.msg);
                    window.setTimeout('window.location = "./user/index.php"', 1000);
                } else {
                    showAlert(json.msg);
                    showMsg('hide', '');
                }
            }
        });
    }
}

var register = function() {
    var uname = $('#reg-uname').val();
    var passwd = $('#reg-passwd').val();
    if (checkForm('register', uname, passwd, $('#reg-passwd2').val())) {
        $.ajax({
            type: "POST",
            url: "ajax.php?action=register",
            dataType: "json",
            data: {'uname':uname, 'passwd':passwd},
            beforeSend: function() {
                showMsg('alert-info', 'Registering...');
            },
            success: function(json) {
                if (json.errno == 0) {
                    showAlert(json.msg, function(){
                        showMsg('hide', '');
                        $('[data-remodal-id=register-modal]').remodal().close();
                        // Automatically login after registeration
                        $('#uname').val(uname);
                        $('#passwd').val(passwd);
                        login();
                    });
                } else {
                    showAlert(json.msg);
                    showMsg('hide', "");
                }
            }
        });
    }
}

function checkForm(type, uname, passwd, passwd2) {
    switch(type) {
        case "login":
            if (uname == "") {
                showMsg('alert-warning', '用户名不能为空哦');
                $("#uname").focus();
                return false;
            } else if (passwd == ""){
                showMsg('alert-warning', '密码不能为空哦');
                $('#passwd').focus();
                return false;
            } else {
                return true;
            }
            break;
        case "register":
            if (uname == "") {
                showMsg('alert-warning', '用户名不能为空哦');
                $('#uname').focus();
                return false;
            } else if (passwd == ""){
                showMsg('alert-warning', '密码不能为空哦');
                $('#passwd').focus();
                return false;
            } else if (passwd2 == ""){
                showMsg('alert-warning', '确认密码不能为空');
                $('#cpasswd').focus();
                return false;
            } else if (passwd != passwd2){
                showMsg('alert-warning', '注册密码和确认密码不一样诶');
                $('#cpasswd').focus();
                return false;
            } else {
                return true;
            }
            break;
        default:
            return false;
    }
}

$('#login').click(function(){
    $('[data-remodal-id=login-modal]').remodal().open();
})

$('#register').click(function(){
    $('[data-remodal-id=register-modal]').remodal().open();
})

// Register Event
$('body').on('keypress', '[data-remodal-id=register-modal]', function(event){
    if (event.which == 13) register();
}).on('click', '#register-button', register);

// Login Event
$('body').on('keypress', '[data-remodal-id=login-modal]', function(event){
    if (event.which == 13) login();
}).on('click', '#login-button', login);

