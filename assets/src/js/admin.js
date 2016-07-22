/*
* @Author: printempw
* @Date:   2016-07-22 14:02:44
* @Last Modified by:   printempw
* @Last Modified time: 2016-07-22 19:27:20
*/

'use strict';

$(document).ready(function() {
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue'
    });
});

$('#layout-skins-list [data-skin]').click(function(e) {
    e.preventDefault();
    var skin_name = $(this).data('skin');
    $('body').removeClass(current_skin).addClass(skin_name);
    current_skin = skin_name;
});

$('#color-submit').click(function() {

    $.ajax({
        type: "POST",
        url: "../admin?action=color",
        dataType: "json",
        data: { "color_scheme": current_skin },
        success: function(json) {
            if (json.errno == 0)
                toastr.success(json.msg);
            else
                toastr.warning(json.msg);
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
});

$('#page-select').on('change', function() {
    window.location = "?page=" + $(this).val();
});

function changeUserEmail(uid) {
    var email = prompt("请输入新邮箱：");

    if (!email) return;

    $.ajax({
        type: "POST",
        url: "../admin?action=email",
        dataType: "json",
        data: { 'uid': uid, 'email': email },
        success: function(json) {
            if (json.errno == 0) {
                $($('tr#'+uid+' > td')[1]).html(email);
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}

function changeUserNickName(uid) {
    var nickname = prompt("请输入新昵称：");

    if (!nickname) return;

    $.ajax({
        type: "POST",
        url: "../admin?action=nickname",
        dataType: "json",
        data: { 'uid': uid, 'nickname': nickname },
        success: function(json) {
            if (json.errno == 0) {
                $($('tr#'+uid+' > td')[2]).html(nickname);
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}

function changeUserPwd(uid) {
    var password = prompt("请输入新密码：");

    if (!password) return;

    $.ajax({
        type: "POST",
        url: "../admin?action=password",
        dataType: "json",
        data: { 'uid': uid, 'password': password },
        success: function(json) {
            if (json.errno == 0)
                toastr.success(json.msg);
            else
                toastr.warning(json.msg);
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}

function changeUserScore(uid, score) {
    $.ajax({
        type: "POST",
        url: "../admin?action=score",
        dataType: "json",
        data: { 'uid': uid, 'score': score },
        success: function(json) {
            if (json.errno == 0) {
                $('tr#'+uid+' > td > .score').val(score);
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}

function deleteUserAccount(uid) {
    if (!window.confirm('真的要删除此用户吗？此操作不可恢复')) return;

    $.ajax({
        type: "POST",
        url: "../admin?action=delete",
        dataType: "json",
        data: { 'uid': uid },
        success: function(json) {
            if (json.errno == 0) {
                $('tr#'+uid).remove();
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}

$('.score').on('keypress', function(event){
    if (event.which == 13)
        changeUserScore($(this).parent().parent().attr('id'), $(this).val());
}).click(function() {
    $(this).tooltip('show');
})
