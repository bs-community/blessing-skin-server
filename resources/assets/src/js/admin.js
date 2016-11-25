/*
 * @Author: printempw
 * @Date:   2016-07-22 14:02:44
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-11-25 12:45:28
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
        url: "./users?action=color",
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
    var email = prompt(trans('admin.newUserEmail'));

    if (!email) return;

    $.ajax({
        type: "POST",
        url: "./users?action=email",
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
    var nickname = prompt(trans('admin.newUserNickname'));

    if (!nickname) return;

    $.ajax({
        type: "POST",
        url: "./users?action=nickname",
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
    var password = prompt(trans('admin.newUserPassword'));

    if (!password) return;

    $.ajax({
        type: "POST",
        url: "./users?action=password",
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
        url: "./users?action=score",
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

function changeBanStatus(uid) {
    $.ajax({
        type: "POST",
        url: "./users?action=ban",
        dataType: "json",
        data: { 'uid': uid },
        success: function(json) {
            if (json.errno == 0) {
                var object = $('#'+uid).find('a#ban');
                var dom = '<a id="ban" href="javascript:changeBanStatus('+uid+');">' +
                            (object.text() == trans('admin.ban') ? trans('admin.unban') : trans('admin.ban')) + '</a>';
                object.html(dom);

                $('#'+uid).find('#permission').text(json.permission == '-1' ? trans('admin.banned') : trans('admin.normal'));
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function changeAdminStatus(uid) {
    $.ajax({
        type: "POST",
        url: "./users?action=admin",
        dataType: "json",
        data: { 'uid': uid },
        success: function(json) {
            if (json.errno == 0) {
                var object = $('#'+uid).find('a#admin');
                var dom = '<a href="javascript:changeAdminStatus('+uid+');">' +
                            (object.text() == trans('admin.setAdmin') ? trans('admin.unsetAdmin') : trans('admin.setAdmin')) + '</a>';
                object.html(dom);

                $('#'+uid).find('#permission').text(json.permission == '1' ? trans('admin.admin') : trans('admin.normal'));
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function deleteUserAccount(uid) {
    if (!window.confirm(trans('admin.deleteUserNotice'))) return;

    $.ajax({
        type: "POST",
        url: "./users?action=delete",
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
        url: "./players?action=preference",
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
                    '<label for="model">'+trans('admin.textureType')+'</label>'+
                    '<select class="form-control" id="model">'+
                        '<option value="steve">'+trans('admin.skin', {'model': 'Steve'})+'</option>'+
                        '<option value="alex">'+trans('admin.skin', {'model': 'Alex'})+'</option>'+
                        '<option value="cape">'+trans('admin.cape')+'</option>'+
                    '</select>'+
                '</div>'+
                '<div class="form-group">'+
                    '<label for="tid">'+trans('admin.pid')+'</label>'+
                    '<input id="tid" class="form-control" type="text" placeholder="'+trans('admin.pidNotice')+'">'+
                '</div>';

    var player_name = $('#'+pid).find('#player-name').text();
    showModal(dom, trans('admin.changePlayerTexture', {'player': player_name}), 'default', 'ajaxChangeTexture('+pid+')');
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
        url: "./players?action=texture",
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
    var uid = prompt(trans('admin.changePlayerOwner'));

    if (!uid) return;

    $.ajax({
        type: "POST",
        url: "./players?action=owner",
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

function deletePlayer(pid) {
    if (!window.confirm(trans('admin.deletePlayerNotice'))) return;

    $.ajax({
        type: "POST",
        url: "./players?action=delete",
        dataType: "json",
        data: { 'pid': pid },
        success: function(json) {
            if (json.errno == 0) {
                $('tr#'+pid).remove();
                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function downloadUpdates() {
    var file_size = 0;
    var progress  = 0;

    console.log("Prepared to download");

    $.ajax({
        url: './update/download?action=prepare-download',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#update-button').html('<i class="fa fa-spinner fa-spin"></i> 正在准备').prop('disabled', 'disabled');
        },
    })
    .done(function(json) {
        console.log(json);

        file_size = json.file_size;

        $('#file-size').html(file_size);

        $('#modal-start-download').modal({
            'backdrop': 'static',
            'keyboard': false
        });

        console.log("started downloading");
        $.ajax({
            url: './update/download?action=start-download',
            type: 'POST',
            dataType: 'json'
        })
        .done(function(json) {
            // set progress to 100 when got the response
            progress = 100;

            console.log("Downloading finished");
            console.log(json);
        })
        .fail(showAjaxError);

        var interval_id = window.setInterval(function() {

            if (progress == 100) {
                clearInterval(interval_id);

                $('#modal-start-download').modal('toggle');

                swal({
                    type: 'success',
                    html: '下载完成！'
                }).then(function(new_name) {
                    //
                });
            } else {
                $.ajax({
                    url: './update/download?action=get-file-size',
                    type: 'GET'
                })
                .done(function(json) {
                    progress = (json.size / file_size * 100).toFixed(2);

                    $('#imported-progress').html(progress);
                    $('.progress-bar').css('width', progress+'%').attr('aria-valuenow', progress);

                    console.log("Progress: "+progress);
                })
                .fail(showAjaxError);
            }

        }, 300);

    })
    .fail(showAjaxError);

}
