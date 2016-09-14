/*
 * @Author: printempw
 * @Date:   2016-07-17 10:54:22
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-09-11 15:26:09
 */

'use strict';

$(document).ready(function() {
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue'
    });
});

function freshCaptcha() {
    $('.captcha').attr('src', './captcha?' + new Date().getTime());
    $('#captcha').val('');
}

var login_fails = 0;

$('#login-button').click(function() {
    var data           = new Object();
    var email_or_uname = $('#email_or_username').val();

    if (/\S+@\S+\.\S+/.test($('#email_or_username').val())) {
        data.email    = email_or_uname;
    } else {
        data.username = email_or_uname;
    }

    data.password = $('#password').val();
    data.keep     = $('#keep').prop('checked') ? true : false;

    if (email_or_uname == "") {
        showMsg('你还没有填写邮箱/角色名哦');
        $('#email_or_username').focus();
    // check valid email address
    } else if (data.password == "") {
        showMsg('密码要好好填哦');
        $('#password').focus();
    } else {
        // if captcha form is shown
        if ($('#captcha-form').css('display') == "block") {
            data.captcha = $("#captcha").val();
            if (data.captcha == "") {
                showMsg('你还没有填写验证码哦');
                $('#captcha').focus();
                return false;
            }
        }

        $.ajax({
            type: "POST",
            url: "./login",
            dataType: "json",
            data: data,
            beforeSend: function() {
                $('#login-button').html('<i class="fa fa-spinner fa-spin"></i> 登录中').prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                    window.setTimeout('window.location = "../user"', 1000);

                } else {
                    if (json.login_fails > 3) {
                        if ($('#captcha-form').css('display') == "none") {
                            swal({
                                type: 'error',
                                html: '你尝试的次数太多啦，请输入验证码'
                            });
                        }

                        $('#captcha-form').show();
                    }

                    freshCaptcha();

                    showMsg(json.msg, 'warning');
                    $('#login-button').html('登录').prop('disabled', '');
                }
            },
            error: function(json) {
                showAjaxError(json);
                $('#login-button').html('登录').prop('disabled', '');
            }
        });
    }
    return false;
});

$('.captcha').click(freshCaptcha);

$('#register-button').click(function() {

    var email    = $('#email').val();
    var password = $('#password').val();
    var nickname = $('#nickname').val();
    var captcha  = $('#captcha').val();

    // check valid email address
    if (email == "") {
        showMsg('你还没有填写邮箱哦');
        $('#email').focus();
    } else if (!/\S+@\S+\.\S+/.test(email)) {
        showMsg('邮箱格式不正确！', 'warning');
    } else if (password == "") {
        showMsg('密码要好好填哦');
        $('#password').focus();
    } else if (password.length < 8 || password.length > 16) {
        showMsg('无效的密码。密码长度应该大于 8 并小于 16。', 'warning');
        $('#password').focus();
    } else if ($('#confirm-pwd').val() == "") {
        showMsg('确认密码不能为空');
        $('#confirm-pwd').focus();
    } else if (password != $('#confirm-pwd').val()) {
        showMsg('密码和确认的密码不一样诶？', 'warning');
        $('#confirm-pwd').focus();
    } else if (nickname == "") {
        showMsg('你还没有填写昵称哦');
        $('#nickname').focus();
    } else if (captcha == "") {
        showMsg('你还没有填写验证码哦');
        $('#captcha').focus();
    } else {

        $.ajax({
            type: "POST",
            url: "./register",
            dataType: "json",
            data: { 'email': email, 'password': password, 'nickname': nickname, 'captcha': captcha },
            beforeSend: function() {
                $('#register-button').html('<i class="fa fa-spinner fa-spin"></i> 注册中').prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                    window.setTimeout('window.location = "../user"', 1000);
                } else {
                    showMsg(json.msg, 'warning');
                    freshCaptcha();
                    $('#register-button').html('注册').prop('disabled', '');
                }
            },
            error: function(json) {
                showAjaxError(json);
                $('#register-button').html('注册').prop('disabled', '');
            }
        });
    }
    return false;

});

$('#forgot-button').click(function() {

    var email    = $('#email').val();
    var captcha  = $('#captcha').val();

    // check valid email address
    if (email == "") {
        showMsg('你还没有填写邮箱哦');
        $('#email').focus();
    } else if (!/\S+@\S+\.\S+/.test(email)) {
        showMsg('邮箱格式不正确！', 'warning');
    } else if (captcha == "") {
        showMsg('你还没有填写验证码哦');
        $('#captcha').focus();
    } else {

        $.ajax({
            type: "POST",
            url: "./forgot",
            dataType: "json",
            data: { 'email': email, 'captcha': captcha },
            beforeSend: function() {
                $('#forgot-button').html('<i class="fa fa-spinner fa-spin"></i> 发送中').prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    showMsg(json.msg, 'success');
                    $('#forgot-button').html('发送').prop('disabled', 'disabled');
                } else {
                    showMsg(json.msg, 'warning');
                    freshCaptcha();
                    $('#forgot-button').html('发送').prop('disabled', '');
                }
            },
            error: function(json) {
                showAjaxError(json);
                $('#forgot-button').html('发送').prop('disabled', '');
            }
        });
    }
    return false;

});

$('#reset-button').click(function() {
    var uid = $('#uid').val();
    var password = $('#password').val();

    if (password == "") {
        showMsg('密码要好好填哦');
        $('#password').focus();
    } else if (password.length < 8 || password.length > 16) {
        showMsg('无效的密码。密码长度应该大于 8 并小于 16。', 'warning');
        $('#password').focus();
    } else if ($('#confirm-pwd').val() == "") {
        showMsg('确认密码不能为空');
        $('#confirm-pwd').focus();
    } else {

        $.ajax({
            type: "POST",
            url: "./reset",
            dataType: "json",
            data: { 'uid': uid, 'password': password },
            beforeSend: function() {
                $('#reset-button').html('<i class="fa fa-spinner fa-spin"></i> 重置中').prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(function() {
                        window.location = "./login";
                    });
                } else {
                    showMsg(json.msg, 'warning');
                    $('#reset-button').html('重置').prop('disabled', '');
                }
            },
            error: function(json) {
                showAjaxError(json);
                $('#reset-button').html('重置').prop('disabled', '');
            }
        });
    }
    return false;
});
