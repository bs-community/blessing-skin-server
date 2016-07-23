/*
* @Author: printempw
* @Date:   2016-07-22 14:02:44
* @Last Modified by:   printempw
* @Last Modified time: 2016-07-23 15:22:01
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
        url: "../admin/users?action=color",
        dataType: "json",
        data: { "color_scheme": current_skin },
        success: function(json) {
            if (json.errno == 0)
                toastr.success(json.msg);
            else
                toastr.warning(json.msg);
        },
        error: showAjaxError
    });
});

$('#page-select').on('change', function() {
    // if has query strings
    if (getQueryString('filter') != "" || getQueryString('q') != "") {
        if (getQueryString('page') == "")
            window.location = location.href + "&page=" + $(this).val();
        else
            window.location = "?filter="+getQueryString('filter')+"&q="+getQueryString('q')+"&page="+$(this).val();
    } else {
        window.location = "?page=" + $(this).val();
    }

});

function changeUserEmail(uid) {
    var email = prompt("请输入新邮箱：");

    if (!email) return;

    $.ajax({
        type: "POST",
        url: "../admin/users?action=email",
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
        error: showAjaxError
    });
}

function changeUserNickName(uid) {
    var nickname = prompt("请输入新昵称：");

    if (!nickname) return;

    $.ajax({
        type: "POST",
        url: "../admin/users?action=nickname",
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
        error: showAjaxError
    });
}

function changeUserPwd(uid) {
    var password = prompt("请输入新密码：");

    if (!password) return;

    $.ajax({
        type: "POST",
        url: "../admin/users?action=password",
        dataType: "json",
        data: { 'uid': uid, 'password': password },
        success: function(json) {
            if (json.errno == 0)
                toastr.success(json.msg);
            else
                toastr.warning(json.msg);
        },
        error: showAjaxError
    });
}

function changeUserScore(uid, score) {
    $.ajax({
        type: "POST",
        url: "../admin/users?action=score",
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
        error: showAjaxError
    });
}

function changePermission(uid) {
    $.ajax({
        type: "POST",
        url: "../admin/users?action=permission",
        dataType: "json",
        data: { 'uid': uid },
        success: function(json) {
            if (json.errno == 0) {
                var object = $($('#'+uid).find('ul').children()[6]);
                var dom = '<a href="javascript:changePermission('+uid+');">' +
                            (object.text() == '封禁' ? '解封' : '封禁') + '</a>';
                object.html(dom);
                $('#'+uid).find('#permission').text(object.text() == '封禁' ? '正常' : '封禁');
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function deleteUserAccount(uid) {
    if (!window.confirm('真的要删除此用户吗？此操作不可恢复')) return;

    $.ajax({
        type: "POST",
        url: "../admin/users?action=delete",
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
        error: showAjaxError
    });
}

$('.score').on('keypress', function(event){
    if (event.which == 13)
        changeUserScore($(this).parent().parent().attr('id'), $(this).val());
}).click(function() {
    $(this).tooltip('show');
});

$('body').on('change', '#preference', function() {
    $.ajax({
        type: "POST",
        url: "../admin/players?action=preference",
        dataType: "json",
        data: { 'pid': $(this).parent().parent().attr('id'), 'preference': $(this).val() },
        success: function(json) {
            if (json.errno == 0) {
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
});

function changeTexture(pid) {
    var dom   = '<div class="form-group">'+
                    '<label for="model">材质类型</label>'+
                    '<select class="form-control" id="model">'+
                        '<option value="steve">皮肤（Steve 模型）</option>'+
                        '<option value="alex">皮肤（Alex 模型）</option>'+
                        '<option value="cape">披风</option>'+
                    '</select>'+
                '</div>'+
                '<div class="form-group">'+
                    '<label for="tid">材质 ID</label>'+
                    '<input id="tid" class="form-control" type="text" placeholder="输入要更换的材质的 TID">'+
                '</div>';

    var player_name = $('#'+pid).find('#player-name').text();
    showModal(dom, '更换角色 '+player_name+' 的材质', 'default', 'ajaxChangeTexture('+pid+')');
    return;
}

function ajaxChangeTexture(pid) {
    // remove interference of modal which is hide
    $('.modal').each(function() {
        if ($(this).css('display') == "none")
            $(this).remove();
    });

    var model = $('#model').val();
    var tid = $('#tid').val();

    $.ajax({
        type: "POST",
        url: "../admin/players?action=texture",
        dataType: "json",
        data: { 'pid': pid, 'model': model, 'tid': tid },
        success: function(json) {
            if (json.errno == 0) {
                $('#'+pid+'-'+model).attr('src', '../preview/64/'+tid+'.png');
                $('.modal').modal('hide');
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function changeOwner(pid) {
    var uid = prompt("请输入此角色要让渡至的用户 UID：");

    if (!uid) return;

    $.ajax({
        type: "POST",
        url: "../admin/players?action=owner",
        dataType: "json",
        data: { 'pid': pid, 'uid': uid },
        success: function(json) {
            if (json.errno == 0) {
                $($('#'+pid).children()[1]).text(uid);
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}
