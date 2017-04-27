/*
 * @Author: printempw
 * @Date:   2016-07-16 10:02:24
 * @Last Modified by: g-plane
 * @Last Modified time: 2017-04-27 16:37:05
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

let selectedTextures = [];

$('body').on('click', '.item-body', function () {
    $('.item-selected').parent().removeClass('item-selected');
    $(this).parent().addClass('item-selected');

    const tid = parseInt($(this).parent().attr('tid'));

    $.ajax({
        type: "POST",
        url: "../skinlib/info/" + tid,
        dataType: "json",
        success: (json) => {
            if (json.type == "cape") {
                MSP.changeCape('../textures/' + json.hash);
                selectedTextures['cape'] = tid;
            } else {
                MSP.changeSkin('../textures/' + json.hash);
                selectedTextures['skin'] = tid;
            }

            if (selectedTextures['skin'] !== undefined && selectedTextures['cape'] !== undefined)
                $('#textures-indicator').text(`${trans('general.skin')} & ${trans('general.cape')}`);
            else if (selectedTextures['skin'] != undefined)
                $('#textures-indicator').text(trans('general.skin'));
            else if (selectedTextures['cape'] != undefined)
                $('#textures-indicator').text(trans('general.cape'));
        },
        error: showAjaxError
    });
});

$('body').on('click', '.category-switch', () => {
    const category = $('a[href="#skin-category"]').parent().hasClass('active') ? 'cape' : 'skin';
    const page = parseInt($('#closet-paginator').attr(`last-${category}-page`));
    const search = $('input[name=q]').val();
    reloadCloset(category, page, search);
});

function renderClosetItemComponent(item) {
    return `
    <div class="item" tid="${item.tid}">
    <div class="item-body">
        <img src="${url('/')}preview/${item.tid}.png">
    </div>
    <div class="item-footer">
        <p class="texture-name">
            <span title="${item.name}">${item.name} <small>(${item.type})</small></span>
        </p>

        <a href="${url('/')}skinlib/show/${item.tid}" title="${trans('user.viewInSkinlib')}" class="more" data-toggle="tooltip" data-placement="bottom"><i class="fa fa-share"></i></a>
        <span title="${trans('general.more')}" class="more" data-toggle="dropdown" aria-haspopup="true" id="more-button"><i class="fa fa-cog"></i></span>

        <ul class="dropup dropdown-menu" aria-labelledby="more-button">
            <li><a href="javascript:renameClosetItem(${item.tid}, '${item.name}');">${trans('user.renameItem')}</a></li>
            <li><a href="javascript:removeFromCloset(${item.tid});">${trans('user.removeItem')}</a></li>
            <li><a href="javascript:setAsAvatar(${item.tid});">${trans('user.setAsAvatar')}</a></li>
        </ul>
    </div>
</div>`;
}

function renderCloset(items, category) {
    const search = $('input[name=q]').val();
    let container = $(`#${category}-category`);
    container.html('');
    if (items.length === 0) {
        $('#closet-paginator').hide();
        if (search === '') {
            container.html(`<div class="empty-msg">
            ${trans('user.emptyClosetMsg', { url: url('skinlib?filter=' + category) })}</div>`);
        } else {
            container.html(`<div class="empty-msg">${trans('general.noResult')}</div>`);
        }
    } else {
        $('#closet-paginator').show();
        for (const item of items) {
            container.append(renderClosetItemComponent(item));
        }
    }
}

function reloadCloset(category, page, search) {
    Promise.resolve($.ajax({
        type: 'GET',
        url: url('user/closet-data'),
        dataType: 'json',
        data: {
            category: category,
            page: page,
            q: search
        }
    })).then(result => {
        renderCloset(result.items, result.category);
        let paginator = $('#closet-paginator');
        paginator.attr(`last-${result.category}-page`, page);
        paginator.jqPaginator('option', {
            currentPage: page,
            totalPages: result.total_pages
        });
    }).catch(error => showAjaxError);
}

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

                    ['skin', 'cape'].forEach(type => {
                        let container = $(`#${type}-category`);
                        if ($.trim(container.html()) == '') {
                            container.html(`<div class="empty-msg">
                            ${trans('user.emptyClosetMsg', { url: url('skinlib?filter=' + type) })}</div>`);
                        }
                    })
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

    if (window.location.pathname.includes('/user/closet')) {
        Promise.resolve($.ajax({
            type: 'GET',
            url: /(^https?.*)\/user\/closet/.exec(window.location.href)[1] + '/user/closet-data',
            dataType: 'json'
        })).then(result => {
            renderCloset(result.items, result.category);
            $('#closet-paginator').jqPaginator({
                totalPages: result.total_pages,
                visiblePages: 5,
                currentPage: 1,
                first: '<li><a style="cursor: pointer;">«</a></li>',
                prev: '<li><a style="cursor: pointer;">‹</a></li>',
                next: '<li><a style="cursor: pointer;">›</a></li>',
                last: '<li><a style="cursor: pointer;">»</a></li>',
                page: '<li><a style="cursor: pointer;">{{page}}</a></li>',
                wrapper: '<ul class="pagination pagination-sm no-margin"></ul>',
                onPageChange: page => {
                    reloadCloset(
                        $('#skin-category').hasClass('active') ? 'skin' : 'cape',
                        page,
                        $('input[name=q]').val()
                    );
                }
            });
        }).catch(error => showAjaxError);

        $('input[name=q]').on('input', debounce(() => {
            const category = $('#skin-category').hasClass('active') ? 'skin' : 'cape';
            reloadCloset(
                category,
                1,
                $('input[name=q]').val()
            );
        }, 350));
    }
});

function setTexture() {
    var pid = 0;

    $('input[name="player"]').each(function(){
        if (this.checked) pid = this.id;
    });

    if (!pid) {
        toastr.info(trans('user.emptySelectedPlayer'));
    } else if (selectedTextures['skin'] == undefined && selectedTextures['cape'] == undefined) {
        toastr.info(trans('user.emptySelectedTexture'));
    } else {
        $.ajax({
            type: "POST",
            url: "./player/set",
            dataType: "json",
            data: {
                'pid': pid,
                'tid[skin]': selectedTextures['skin'],
                'tid[cape]': selectedTextures['cape']
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
        text: $('#player_name').attr('placeholder'),
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
    let dom = `
    <div class="form-group">
        <input type="checkbox" id="clear-steve"> Default (Steve)
    </div>
    <div class="form-group">
        <input type="checkbox" id="clear-alex"> Slim (Alex)
    </div>
    <div class="form-group">
        <input type="checkbox" id="clear-cape"> ${trans('general.cape')}
    </div>
    <script>
        $('input[type=checkbox]').iCheck({ checkboxClass: 'icheckbox_square-blue' });
    </script>
    `;
    showModal(dom, trans('user.chooseClearTexture'), 'default', { callback: `ajaxClearTexture(${pid})` });
    return;
}

function ajaxClearTexture(pid) {
    $('.modal').each(function () {
        if ($(this).css('display') == "none")
            $(this).remove();
    });

    let data = { pid: pid };
    ['steve', 'alex', 'cape'].forEach(type => {
        data[type] = $(`#clear-${type}`).prop('checked') ? 1 : 0;
    });

    if (data['steve'] == 0 && data['alex'] == 0 && data['cape'] == 0) {
        toastr.warning(trans('user.noClearChoice'));
        return;
    }

    Promise.resolve($.ajax({
        type: 'POST',
        url: './player/texture/clear',
        dataType: 'json',
        data: data
    })).then(json => {
        swal({ type: json.errno == 0 ? 'success' : 'error', html: json.msg });
        $('.modal').modal('hide');
    }).catch(error => showAjaxError);
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
    let domOldPwd = $('#password');
    let domNewPwd = $('#new-passwd');
    let domConfirmPwd = $('#confirm-pwd');

    let password = domOldPwd.val();
    let new_passwd = domNewPwd.val();

    if (password == "") {
        toastr.info(trans('user.emptyPassword'));
        domOldPwd.focus();
    } else if (new_passwd == "") {
        toastr.info(trans('user.emptyNewPassword'));
        domNewPwd.focus();
    } else if (domConfirmPwd.val() == "") {
        toastr.info(trans('auth.emptyConfirmPwd'));
        domConfirmPwd.focus();
    } else if (new_passwd != domConfirmPwd.val()) {
        toastr.warning(trans('auth.invalidConfirmPwd'));
        domConfirmPwd.focus();
    } else {
        Promise.resolve($.ajax({
            type: "POST",
            url: "./profile?action=password",
            dataType: "json",
            data: { 'current_password': password, 'new_password': new_passwd }
        })).then(result => {
            if (result.errno == 0) {
                return swal({ type: 'success', text: result.msg })
                    .then(() => {
                        return logout();
                    }).then(result => {
                        if (result.errno == 0) {
                            window.location = url('auth/login');
                        }
                    }).catch(error => showAjaxError);
            } else {
                return swal({ type: 'warning', text: result.msg });
            }
        }).catch(error => showAjaxError);
    }
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
    }).then(() => {
        return Promise.resolve($.ajax({
            type: 'POST',
            url: './profile?action=email',
            dataType: 'json',
            data: { 'new_email': new_email, 'password': $('#current-password').val() }
        }));
    }).then(result => {
        if (result.errno == 0) {
            return swal({ type: 'success', text: result.msg })
                .then(() => {
                    return logout();
                }).then(result => {
                    if (result.errno == 0) {
                        window.location = url('auth/login');
                    }
                }).catch(error => showAjaxError);
        } else {
            return swal({ type: 'warning', text: result.msg });
        }
    }).catch(error => showAjaxError);
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
