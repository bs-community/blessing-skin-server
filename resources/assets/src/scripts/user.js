/*
 * @Author: printempw
 * @Date:   2016-07-16 10:02:24
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-20 21:19:07
 */

'use strict';

$('body').on('click', '.player', function () {
    $('.player-selected').removeClass('player-selected');
    $(this).addClass('player-selected');

    showPlayerTexturePreview(this.id);
});

$('body').on('click', '#preview-switch', () => {
    TexturePreview.previewType == '3D' ? TexturePreview.show2dPreview() : TexturePreview.show3dPreview();
});

var selected = [];

$('body').on('click', '.item-body', function () {
    $('.item-selected').parent().removeClass('item-selected');
    $(this).parent().addClass('item-selected');

    let tid = $(this).parent().attr('tid');

    $.ajax({
        type: "POST",
        url: "../skinlib/info/" + tid,
        dataType: "json",
        success: (json) => {
            if (json.type == "cape") {
                MSP.changeCape('../textures/' + json.hash);
                selected['cape'] = tid;
            } else {
                MSP.changeSkin('../textures/' + json.hash);
                selected['skin'] = tid;
            }

            selected.length = 0;

            ['skin', 'cape'].forEach((key) => {
                if (selected[key] !== undefined) selected.length++;

                $('#textures-indicator').html(selected.length);
            });
        },
        error: showAjaxError
    });
});

function showPlayerTexturePreview(pid) {
    $.ajax({
        type: "POST",
        url: url('user/player/show'),
        dataType: "json",
        data: { "pid": pid },
        success: (json) => {

            ['steve', 'alex', 'cape'].forEach((type) => {
                let tid     = json[`tid_${type}`];
                let preview = new TexturePreview(type, tid, json.preference);

                if (tid) {
                    preview.change2dPreview().change3dPreview();
                } else {
                    preview.showNotUploaded();
                }
            });

            if ((json.preference == "default" && !json.tid_steve) || (json.preference == "slim" && !json.tid_alex)) {
                // show default skin
                MSP.changeSkin(defaultSkin);
            }

            console.log(`Texture previews of player ${json.player_name} rendered.`);
        },
        error: showAjaxError
    });
}

function renameClosetItem(tid, oldName) {
    swal({
        title: trans('user.renameClosetItem'),
        input: 'text',
        inputValue: oldName,
        showCancelButton: true,
        inputValidator: function(value) {
            return new Promise(function(resolve, reject) {
                if (value) {
                    resolve();
                } else {
                    reject(trans('skinlib.emptyNewTextureName'));
                }
            });
        }
    }).then(function(new_name) {
        $.ajax({
            type: "POST",
            url: "./closet/rename",
            dataType: "json",
            data: { 'tid': tid, 'new_name': new_name },
            success: function(json) {
                if (json.errno == 0) {
                    $("[tid="+tid+"]>.item-footer>.texture-name>span").html(new_name);
                    toastr.success(json.msg);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}

function removeFromCloset(tid) {
    swal({
        text: trans('user.removeFromClosetNotice'),
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
        title: trans('user.setAvatar'),
        text: trans('user.setAvatarNotice'),
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
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    });
});

function setTexture() {
    var pid = 0;

    $('input[name="player"]').each(function(){
        if (this.checked) pid = this.id;
    });

    if (!pid) {
        toastr.info(trans('user.emptySelectedPlayer'));
    } else if (selected.length == 0) {
        toastr.info(trans('user.emptySelectedTexture'));
    } else {
        $.ajax({
            type: "POST",
            url: "./player/set",
            dataType: "json",
            data: {
                'pid': pid,
                'tid[skin]': selected['skin'],
                'tid[cape]': selected['cape']
            },
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
            error: showAjaxError
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

function changePlayerName(pid, current_player_name) {
    swal({
        title: trans('user.changePlayerName'),
        text: trans('user.playerNameRule'),
        inputValue: current_player_name,
        input: 'text',
        showCancelButton: true,
        inputValidator: function(value) {
            return new Promise(function(resolve, reject) {
                if (value) {
                    resolve();
                } else {
                    reject(trans('user.emptyPlayerName'));
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
        text: trans('user.clearTexture'),
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
        title: trans('user.deletePlayer'),
        text: trans('user.deletePlayerNotice'),
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
                    }).then(function() {
                        $('tr#'+pid).remove();
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
                    location.reload();
                });
                $('#modal-add-player').modal('hide');
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
            html: trans('user.emptyNewNickName')
        });
        return;
    }

    swal({
        text: trans('user.changeNickName', { new_nickname: new_nickname }),
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
        toastr.info(trans('user.emptyPassword'));
        $('#passwd').focus();
    } else if (new_passwd == "") {
        toastr.info(trans('user.emptyNewPassword'));
        $('#new-passwd').focus();
    } else if ($('#confirm-pwd').val() == "") {
        toastr.info(trans('auth.emptyConfirmPwd'));
        $('#confirm-pwd').focus();
    } else if (new_passwd != $('#confirm-pwd').val()) {
        toastr.warning(trans('auth.invalidConfirmPwd'));
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
            html: trans('user.emptyNewEmail')
        });
        return;
    }
    // check valid email address
    if (!/\S+@\S+\.\S+/.test(new_email)) {
        swal({
            type: 'warning',
            html: trans('auth.invalidEmail')
        }); return;
    }

    swal({
        text: trans('user.changeEmail', { new_email: new_email }),
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
            html: trans('user.emptyDeletePassword')
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
                    window.location = "../auth/login";
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

function signIn() {
    $.ajax({
        type: "POST",
        url: "./user/sign-in",
        dataType: "json",
        success: function(json) {
            if (json.errno == 0) {
                $('#score').html(json.score);
                var dom = '<i class="fa fa-calendar-check-o"></i> &nbsp;' + trans('user.signInRemainingTime', { time: String(json.remaining_time) });
                $('#sign-in-button').attr('disabled', 'disabled').html(dom);

                if (json.storage.used > 1024) {
                    $('#user-storage').html(`<b>${Math.round(json.storage.used)}</b>/ ${Math.round(json.storage.total)} MB`);
                } else {
                    $('#user-storage').html(`<b>${Math.round(json.storage.used)}</b>/ ${Math.round(json.storage.total)} KB`);
                }

                $('#user-storage-bar').css('width', `${json.storage.percentage}%`)

                swal({
                    type: 'success',
                    html: json.msg
                });
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}
