/*
 * @Author: printempw
 * @Date:   2016-07-16 10:02:24
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-14 13:03:37
 */

'use strict';

$('body').on('click', '.player', function() {
    $('.player-selected').removeClass('player-selected');
    $(this).addClass('player-selected');

    var pid = this.id;

    $.ajax({
        type: "POST",
        url: "./player/show",
        dataType: "json",
        data: { "pid": pid },
        success: function(json) {
            if ((json.preference == "default" && !json.tid_steve) || (json.preference == "slim" && !json.tid_alex)) {
                MSP.changeSkin(dskin);
            } else {
                if (json.tid_steve) {
                    getHashFromTid(json.tid_steve, function(hash) {
                        $('#steve').attr('src', '../preview/200/' + json.tid_steve + '.png').show();
                        $('#steve').parent().attr('href', '../skinlib/show?tid=' + json.tid_steve);
                        $('#steve').parent().next().hide();
                        if (json.preference == "default")
                            MSP.changeSkin('../textures/' + hash);
                    });
                } else {
                    $('#steve').hide().parent().next().show();
                }

                if (json.tid_alex) {
                    getHashFromTid(json.tid_alex, function(hash) {
                        $('#alex').attr('src', '../preview/200/' + json.tid_alex + '.png').show();
                        $('#alex').parent().attr('href', '../skinlib/show?tid=' + json.tid_alex);
                        $('#alex').parent().next().hide();
                        if (json.preference == "slim")
                            MSP.changeSkin('../textures/' + hash);
                    });
                } else {
                    $('#alex').hide().parent().next().show();
                }
            }

            if (json.tid_cape) {
                getHashFromTid(json.tid_cape, function(hash) {
                    $('#cape').attr('src', '../preview/200/' + json.tid_cape + '.png').show();
                    $('#cape').parent().attr('href', '../skinlib/show?tid=' + json.tid_cape);
                    $('#cape').parent().next().hide();
                    MSP.changeCape('../textures/' + hash);
                });
            } else {
                $('#cape').hide().parent().next().show();
                MSP.changeCape('');
            }
        },
        error: showAjaxError
    });
});

function getHashFromTid(tid, callback) {
    $.ajax({
        type: "GET",
        url: "../skinlib/info/" + tid,
        dataType: "json",
        success: function(json) {
            callback(json.hash)
        },
        error: showAjaxError
    });
}

var preview_type = "3d";

function init3dCanvas() {
    if (preview_type == "2d") return;
    $('#preview-2d').hide();
    if ($(window).width() < 800) {
        var canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    } else {
        var canvas = MSP.get3dSkinCanvas(350, 350);
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    }
}

function show2dPreview() {
    preview_type = "2d";
    $('#canvas3d').remove();
    $('#preview-msg').remove();
    $('.operations').hide();
    $('#preview-2d').show();
    $('#preview-switch').html('切换 3D 预览').attr('onclick', 'show3dPreview();');
}

function show3dPreview() {
    if (isMobile() && preview_type == "2d") {
        $("#skinpreview").append($('<div id="preview-msg" class="alert alert-info alert-dismissible fade in"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>手机上的 3D 预览可能会出现奇怪的问题（譬如空白一片），亟待解决。</div>'));
    }
    preview_type = "3d";
    init3dCanvas();
    $('#preview-2d').hide();
    $('.operations').show();
    $('#preview-switch').html('切换 2D 预览').attr('onclick', 'show2dPreview();');
}

// Change 3D preview status
$('.fa-pause').click(function(){
    MSP.setStatus("movements", !MSP.getStatus("movements"));
    if ($(this).hasClass('fa-pause'))
        $(this).removeClass('fa-pause').addClass('fa-play');
    else
        $(this).removeClass('fa-play').addClass('fa-pause');
});
$('.fa-forward').click(function(){
    MSP.setStatus("running", !MSP.getStatus("running"));
});
$('.fa-repeat').click(function(){
    MSP.setStatus("rotation", !MSP.getStatus("rotation"));
});

$('body').on('click', '.item', function() {
    $('.item-selected').removeClass('item-selected');
    $(this).addClass('item-selected');

    var tid = $(this).attr('tid');

    $.ajax({
        type: "POST",
        url: "../skinlib/info/" + tid,
        dataType: "json",
        success: function(json) {
            if (json.type == "cape") {
                MSP.changeCape('../textures/' + json.hash);
            } else {
                MSP.changeSkin('../textures/' + json.hash);
            }
        },
        error: showAjaxError
    });
});

function removeFromCloset(tid) {
    swal({
        text: '确定要从衣柜中移除此材质吗？',
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "./closet/remove",
            dataType: "json",
            data: { 'tid' : tid },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });

                    $('div[tid='+tid+']').remove();
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}

function setAsAvatar(tid) {
    swal({
        title: '确定要将此材质设置为用户头像吗？',
        text: '将会自动截取皮肤头部',
        type: 'question',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "./profile/avatar",
            dataType: "json",
            data: { "tid": tid },
            success: function(json) {
                if (json.errno == 0) {
                    toastr.success(json.msg);
                    // refersh avatars
                    $('[alt="User Image"]').each(function() {
                        $(this).prop('src', $(this).attr('src') + '?' + new Date().getTime());
                    })
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}

$(document).ready(function() {
    $('input[type=radio]').iCheck({
        radioClass: 'iradio_square-blue'
    });
    swal.setDefaults({
        confirmButtonText: '确定',
        cancelButtonText: '取消'
    });
});

function setTexture() {
    var pid = 0;

    $('input[name="player"]').each(function(){
        if (this.checked) pid = this.id;
    });

    var tid = $('.item-selected').attr('tid');

    if (!pid) {
        toastr.info('你还没有选择角色哦');
    } else if (!tid) {
        toastr.info('你还没有选择要应用的材质哦');
    } else {
        $.ajax({
            type: "POST",
            url: "./player/set",
            dataType: "json",
            data: { 'pid' : pid, 'tid' : tid },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                    $('#modal-use-as').modal('hide');
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: function(json) {
                showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
            }
        });
    }
}

$('body').on('change', '#preference', function() {

    $.ajax({
        type: "POST",
        url: "./player/preference",
        dataType: "json",
        data: { 'pid' : $(this).attr('pid'), 'preference' : $(this).val() },
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

function changePlayerMame(pid, current_player_name) {
    swal({
        title: '请输入角色名：',
        text: '允许数字、字母以及下划线，是否支持中文角色名请参考本站设置',
        inputValue: current_player_name,
        input: 'text',
        showCancelButton: true,
        inputValidator: function(value) {
            return new Promise(function(resolve, reject) {
                if (value) {
                    resolve();
                } else {
                    reject('你还没有填写名称哦');
                }
            });
        }
    }).then(function(new_player_name) {
        $.ajax({
            type: "POST",
            url: "./player/rename",
            dataType: "json",
            data: { 'pid' : pid, 'new_player_name' : new_player_name },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                    $('td:contains("'+pid+'")').next().html(new_player_name);
                } else {
                    swal({
                        type: 'error',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    });
}

function clearTexture(pid) {
    swal({
        text: "确定要重置该用户的皮肤/披风吗？",
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "./player/texture/clear",
            dataType: "json",
            data: { 'pid' : pid },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                } else {
                    swal({
                        type: 'error',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    });
}

function deletePlayer(pid) {
    swal({
        title: "真的要删除该玩家吗？",
        text: "这将是永久性的删除",
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#3085d6',
        confirmButtonColor: '#d33'
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "./player/delete",
            dataType: "json",
            data: { 'pid' : pid },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                } else {
                    swal({
                        type: 'error',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    });
}

function addNewPlayer() {
    $.ajax({
        type: "POST",
        url: "./player/add",
        dataType: "json",
        data: { 'player_name' : $('#player_name').val() },
        success: function(json) {
            if (json.errno == 0) {
                swal({
                    type: 'success',
                    html: json.msg
                }).then(function() {
                    $('#modal-add-player').modal('hide');
                    location.reload();
                });
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function changeNickName() {
    var new_nickname = $('#new-nickname').val();

    if (!new_nickname) {
        swal({
            type: 'error',
            html: '你还没有填写新昵称啊'
        });
        return;
    }

    swal({
        text: '确定要将昵称设置为 ' + new_nickname + ' 吗？',
        type: 'question',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "./profile?action=nickname",
            dataType: "json",
            data: { 'new_nickname' : new_nickname },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                    $('.nickname').each(function() {
                        $(this).html(new_nickname);
                    });
                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    });


}

function changePassword() {

    var password = $('#password').val();
    var new_passwd = $('#new-passwd').val();

    if (password == "") {
        toastr.info('原密码不能为空');
        $('#passwd').focus();
    } else if (new_passwd == "") {
        toastr.info('新密码要好好填哦');
        $('#new-passwd').focus();
    } else if ($('#confirm-pwd').val() == "") {
        toastr.info('确认密码不能为空');
        $('#confirm-pwd').focus();
    } else if (new_passwd != $('#confirm-pwd').val()) {
        toastr.warning('新密码和确认的密码不一样诶？');
        $('#confirm-pwd').focus();
    } else {
        $.ajax({
            type: "POST",
            url: "./profile?action=password",
            dataType: "json",
            data: { 'current_password': password, 'new_password': new_passwd},
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(function() {
                        logout(true, function() {
                            window.location = "../auth/login";
                        });
                    });
                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    }
    return false;
}

$('#new-email').focusin(function() {
    $('#current-password').parent().show();
}).focusout(function() {
    window.setTimeout(function() {
        if (!$('#current-password').is(':focus'))
            $('#current-password').parent().hide();
    }, 10);
})

function changeEmail() {
    var new_email = $('#new-email').val();

    if (!new_email) {
        swal({
            type: 'error',
            html: '你还没有填写新邮箱啊'
        });
        return;
    }
    // check valid email address
    if (!/\S+@\S+\.\S+/.test(new_email)) {
        swal({
            type: 'warning',
            html: '邮箱格式不正确'
        }); return;
    }

    swal({
        text: '确定要将用户邮箱更改为 '+new_email+' 吗？',
        type: 'question',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "./profile?action=email",
            dataType: "json",
            data: { 'new_email' : new_email, 'password' : $('#current-password').val() },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(function() {
                        logout(true, function() {
                            window.location = "../auth/login";
                        });
                    });
                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    });
}

function deleteAccount() {
    var password = $('.modal-body>#password').val();

    if (!password) {
        swal({
            type: 'warning',
            html: '请先输入当前用户密码'
        }); return;
    }

    $.ajax({
        type: "POST",
        url: "./profile?action=delete",
        dataType: "json",
        data: { 'password' : password },
        success: function(json) {
            if (json.errno == 0) {
                swal({
                    type: 'success',
                    html: json.msg
                }).then(function() {
                    logout(true, function() {
                        window.location = "../auth/login";
                    });
                });
            } else {
                swal({
                    type: 'warning',
                    html: json.msg
                });
            }
        },
        error: showAjaxError
    });
}

$('#mod-select').change(function() {
    $('#version-select').children().each(function() { $(this).remove(); });

    if ($(this).val() == "csl") {
        $('#version-select').append('<option value="13_1-upper">13.1 版及以上（推荐）</option>');
        $('#version-select').append('<option value="13_1-lower">13.1 版以下</option>');
    } else if ($(this).val() == "usm") {
        $('#version-select').append('<option value="1_4-upper">1.4 版及以上（推荐）</option>');
        $('#version-select').append('<option value="1_2-1_3">1.2 及 1.3 版</option>');
        $('#version-select').append('<option value="1_2-lower">1.2 版以下</option>');
    }

    showConfig();
});

function showConfig() {
    $('#config-13_1-upper').hide();
    $('#config-13_1-lower').hide();
    $('#config-1_4-upper').hide();
    $('#config-1_2-1_3').hide();
    $('#config-1_2-lower').hide();
    $('#config-'+$('#version-select').val()).show();
}

$('#version-select').change(showConfig);

function sign() {
    $.ajax({
        type: "POST",
        url: "../user/sign",
        dataType: "json",
        success: function(json) {
            if (json.errno == 0) {
                swal({
                    type: 'success',
                    html: json.msg
                }).then(function() {
                    $('#score').html(json.score);
                    var dom = '<i class="fa fa-calendar-check-o"></i> &nbsp;'+json.remaining_time+' 小时后可签到';
                    $('#sign-button').attr('disabled', 'disabled').html(dom);
                });
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}
