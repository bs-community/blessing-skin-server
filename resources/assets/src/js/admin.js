'use strict';

let pluginsTable;

$(document).ready(function() {
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue'
    });
    swal.setDefaults({
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    });

    $.extend(true, $.fn.dataTable.defaults, {
        language: trans('common.datatables'),
        scrollX: true,
        pageLength: 25,
        autoWidth: false,
        processing: true,
        serverSide: true
    });

    if (window.location.href.indexOf(url('admin/users')) >= 0) {
        initUsersTable();
    } else if (window.location.href.indexOf(url('admin/players')) >= 0) {
        initPlayersTable();
    } else if (window.location.href.indexOf(url('admin/plugins/manage')) >= 0) {
        pluginsTable = initPluginsTable();
    }
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
        url: "./customize?action=color",
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

function changeUserEmail(uid) {
    let dom = $(`tr#user-${uid} > td:nth-child(2)`);
    swal({
        text: trans('admin.newUserEmail'),
        showCancelButton: true,
        input: 'text',
        inputValue: dom.text()
    }).then(email => {
        $.ajax({
            type: "POST",
            url: "./users?action=email",
            dataType: "json",
            data: { 'uid': uid, 'email': email },
            success: json => {
                if (json.errno == 0) {
                    dom.text(email);
                    toastr.success(json.msg);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}

function changeUserNickName(uid) {
    let dom = $(`tr#user-${uid} > td:nth-child(3)`);
    swal({
        text: trans('admin.newUserNickname'),
        showCancelButton: true,
        input: 'text',
        inputValue: dom.text()
    }).then(nickname => {
        $.ajax({
            type: "POST",
            url: "./users?action=nickname",
            dataType: "json",
            data: { 'uid': uid, 'nickname': nickname },
            success: json => {
                if (json.errno == 0) {
                    dom.text(nickname);
                    toastr.success(json.msg);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}

function changeUserPwd(uid) {
    swal({
        text: trans('admin.newUserPassword'),
        showCancelButton: true,
        input: 'password',
    }).then(password => {
        return Promise.resolve($.ajax({
            type: "POST",
            url: "./users?action=password",
            dataType: "json",
            data: { 'uid': uid, 'password': password }
        }));
    }).then(json => {
        if (json.errno == 0)
            toastr.success(json.msg);
        else
            toastr.warning(json.msg);
    }).catch(error => showAjaxError);
}

function changeUserScore(uid, score) {
    $.ajax({
        type: "POST",
        url: "./users?action=score",
        dataType: "json",
        // handle id formatted as '#user-1234'
        data: { 'uid': uid.slice(5), 'score': score },
        success: function(json) {
            if (json.errno == 0) {
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
                let dom = $(`#ban-${uid}`);

                if (dom.attr('data') == 'banned') {
                    dom.text(trans('admin.ban')).attr('data', 'normal');
                } else {
                    dom.text(trans('admin.unban')).attr('data', 'banned');
                }

                $(`#user-${uid} > td.status`).text(
                    json.permission == -1 ? trans('admin.banned') : trans('admin.normal')
                );

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
                let dom = $(`#admin-${uid}`);

                if (dom.attr('data') == 'admin') {
                    dom.text(trans('admin.setAdmin')).attr('data', 'normal');
                } else {
                    dom.text(trans('admin.unsetAdmin')).attr('data', 'admin');
                }

                $(`#user-${uid} > td.status`).text(
                    json.permission == 1 ? trans('admin.admin') : trans('admin.normal')
                );

                toastr.success(json.msg);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function deleteUserAccount(uid) {
    swal({
        text: trans('admin.deleteUserNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => {
        return Promise.resolve($.ajax({
            type: "POST",
            url: "./users?action=delete",
            dataType: "json",
            data: { 'uid': uid }
        }))
    }).then(json => {
        if (json.errno == 0) {
            $('tr#user-' + uid).remove();
            toastr.success(json.msg);
        } else {
            toastr.warning(json.msg);
        }
    }).catch(error => showAjaxError);
}

$('body').on('keypress', '.score', function(event){
    if (event.which == 13) {
        changeUserScore($(this).parent().parent().attr('id'), $(this).val());
        $(this).blur();
    }
});

function changePreference() {
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
}

function changeTexture(pid, playerName) {
    let dom   = `
    <div class="form-group">
        <label for="model">${trans('admin.textureType')}</label>
        <select class="form-control" id="model">
            <option value="steve">${trans('admin.skin', {'model': 'Steve'})}</option>
            <option value="alex">${trans('admin.skin', {'model': 'Alex'})}</option>
            <option value="cape">${trans('admin.cape')}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="tid">${trans('admin.pid')}</label>
        <input id="tid" class="form-control" type="text" placeholder="${trans('admin.pidNotice')}">
    </div>`;

    showModal(dom, trans('admin.changePlayerTexture', {'player': playerName}), 'default', {
        callback: `ajaxChangeTexture(${pid})`
    });
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

function changePlayerName(pid, oldName) {
    let dom = $(`tr#${pid} > td:nth-child(3)`);
    swal({
        text: trans('admin.changePlayerNameNotice'),
        input: 'text',
        inputValue: oldName,
        inputValidator: name => {
            return new Promise((resolve, reject) => {
                if (name) {
                    resolve();
                } else {
                    reject(trans('admin.emptyPlayerName'));
                }
            })
        }
    }).then(name => {
        return Promise.resolve($.ajax({
            type: 'POST',
            url: './players?action=name',
            dataType: 'json',
            data: { pid: pid, name: name }
        }));
    }).then(json => {
        if (json.errno == 0) {
            dom.text(json.name);
            toastr.success(json.msg);
        } else {
            toastr.warning(json.msg);
        }
    }).catch(error => showAjaxError);
}

function changeOwner(pid) {
    let dom = $(`#${pid} > td:nth-child(2)`);
    swal({
        html: `${trans('admin.changePlayerOwner')}<br><small>&nbsp;</small>`,
        input: 'number',
        inputValue: dom.text(),
        showCancelButton: true
    }).then(uid => {
        $.ajax({
            type: "POST",
            url: "./players?action=owner",
            dataType: "json",
            data: { 'pid': pid, 'uid': uid },
            success: function (json) {
                if (json.errno == 0) {
                    dom.text(uid);
                    toastr.success(json.msg);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });

    $('.swal2-input').on('input', debounce(() => {
        const uid = $('.swal2-input').val();
        if (uid > 0) {
            Promise.resolve($.ajax({
                type: 'GET',
                url: `./user/${uid}`,
                dataType: 'json'
            })).then(result => {
                $('.swal2-content').html(
                    trans('admin.changePlayerOwner') +
                    '<small style="display: block; margin-top: .5em;">' +
                    trans('admin.targetUser', { nickname: result.user.nickname }) +
                    '</small>'
                );
            }).catch(() => {
                $('.swal2-content').html(`${trans('admin.changePlayerOwner')}<br><small>${trans('admin.noSuchUser')}</small>`);
            });
        }
    }, 350));
}

function deletePlayer(pid) {
    swal({
        text: trans('admin.deletePlayerNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => {
        return Promise.resolve($.ajax({
            type: "POST",
            url: "./players?action=delete",
            dataType: "json",
            data: { 'pid': pid },
            success: function (json) {

            },
            error: showAjaxError
        }))
    }).then(json => {
        if (json.errno == 0) {
            $('tr#' + pid).remove();
            toastr.success(json.msg);
        } else {
            toastr.warning(json.msg);
        }
    }).catch(error => showAjaxError);
}

function enablePlugin(name) {
    $.ajax({
        type: "POST",
        url: "?action=enable&name=" + name,
        dataType: "json",
        success: function(json) {
            if (json.errno == 0) {
                toastr.success(json.msg);

                pluginsTable.ajax.reload(null, false);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function disablePlugin(name) {
    $.ajax({
        type: "POST",
        url: "?action=disable&name=" + name,
        dataType: "json",
        success: function(json) {
            if (json.errno == 0) {
                toastr.warning(json.msg);

                pluginsTable.ajax.reload(null, false);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function deletePlugin(name) {
    swal({
        text: trans('admin.confirmDeletion'),
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "?action=delete&name=" + name,
            dataType: "json",
            success: function(json) {
                if (json.errno == 0) {
                    toastr.success(json.msg);

                    pluginsTable.ajax.reload(null, false);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
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
            $('#update-button').html('<i class="fa fa-spinner fa-spin"></i> '+trans('admin.preparing')).prop('disabled', 'disabled');
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

            $('#imported-progress').html(progress);
            $('.progress-bar').css('width', progress+'%').attr('aria-valuenow', progress);

            if (progress == 100) {
                clearInterval(interval_id);

                $('.modal-title').html('<i class="fa fa-spinner fa-spin"></i> ' + trans('admin.extracting'));
                $('.modal-body').append('<p>'+trans('admin.downloadCompleted')+'</p>')

                console.log("Start extracting");
                $.ajax({
                    url: './update/download?action=extract',
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(json) {
                    console.log("Package extracted and files are covered");
                    $('#modal-start-download').modal('toggle');

                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(function() {
                        window.location = "../";
                    }, function(dismiss) {
                        window.location = "../";
                    });
                })
                .fail(showAjaxError);

            } else {
                $.ajax({
                    url: './update/download?action=get-file-size',
                    type: 'GET'
                })
                .done(function(json) {
                    progress = (json.size / file_size * 100).toFixed(2);

                    console.log("Progress: "+progress);
                })
                .fail(showAjaxError);
            }

        }, 300);

    })
    .fail(showAjaxError);

}

function initUsersTable() {
    let dataUrl = url('admin/user-data');
    if (getQueryString('uid')) {
        dataUrl += '?uid=' + getQueryString('uid');
    }
    $('#user-table').DataTable({
        ajax: dataUrl,
        scrollY: ($('.content-wrapper').height() - $('.content-header').outerHeight()) * 0.7,
        rowCallback: (row, data) => {
            $(row).attr('id', `user-${data.uid}`);
        },
        columnDefs: [
            {
                targets: 0,
                data: 'uid',
                width: '1%'
            },
            {
                targets: 1,
                data: 'email'
            },
            {
                targets: 2,
                data: 'nickname'
            },
            {
                targets: 3,
                data: 'score',
                render: data => {
                    return `<input type="number" class="form-control score" value="${data}" title="${trans('admin.scoreTip')}" data-toggle="tooltip" data-placement="right">`;
                }
            },
            {
                targets: 4,
                data: 'players_count',
                render: (data, type, row) => {
                    return `<span title="${trans('admin.doubleClickToSeePlayers')}"
                    style="cursor: pointer;"
                    ondblclick="window.location.href = '${url('admin/players?uid=') + row.uid}'"
                    data-toggle="tooltip" data-placement="top">${data}</span>`;
                }
            },
            {
                targets: 5,
                data: 'permission',
                className: 'status',
                render: data => {
                    switch (data) {
                        case -1:
                            return trans('admin.banned');
                        case 0:
                            return trans('admin.normal');
                        case 1:
                            return trans('admin.admin');
                        case 2:
                            return trans('admin.superAdmin');
                    }
                }
            },
            {
                targets: 6,
                data: 'register_at'
            },
            {
                targets: 7,
                data: 'operations',
                searchable: false,
                orderable: false,
                render: (data, type, row) => {
                    let operationsHtml, adminOption = '', bannedOption = '', deleteUserButton;
                    if (row.permission !== 2) {
                        if (data === 2) {
                            if (row.permission === 1) {
                                adminOption = `<li class="divider"></li>
                                <li><a id="admin-${row.uid}" data="admin" href="javascript:changeAdminStatus(${row.uid});">${trans('admin.unsetAdmin')}</a></li>`;
                            } else {
                                adminOption = `<li class="divider"></li>
                                <li><a id="admin-${row.uid}" data="normal" href="javascript:changeAdminStatus(${row.uid});">${trans('admin.setAdmin')}</a></li>`;
                            }
                        }
                        if (row.permission === -1) {
                            bannedOption = `<li class="divider"></li>
                            <li><a id="ban-${row.uid}" data="banned" href="javascript:changeBanStatus(${row.uid});">${trans('admin.unban')}</a></li>`;
                        } else {
                            bannedOption = `<li class="divider"></li>
                            <li><a id="ban-${row.uid}" data="normal" href="javascript:changeBanStatus(${row.uid});">${trans('admin.ban')}</a></li>`;
                        }
                    }

                    if (data === 2) {
                        if (row.permission === 2) {
                            deleteUserButton = `
                            <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="${trans('admin.cannotDeleteSuperAdmin')}">${trans('admin.deleteUser')}</a>`;
                        } else {
                            deleteUserButton = `
                            <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount(${row.uid});">${trans('admin.deleteUser')}</a>`;
                        }
                    } else {
                        if (row.permission === 1 || row.permission === 2) {
                            deleteUserButton = `
                            <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="${trans('admin.cannotDeleteAdmin')}">${trans('admin.deleteUser')}</a>`;
                        } else {
                            deleteUserButton = `
                            <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount(${row.uid});">${trans('admin.deleteUser')}</a>`;
                        }
                    }

                    return `
                    <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    ${trans('admin.operationsTitle')} <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:changeUserEmail(${row.uid});">${trans('admin.changeEmail')}</a></li>
                        <li><a href="javascript:changeUserNickName(${row.uid});">${trans('admin.changeNickName')}</a></li>
                        <li><a href="javascript:changeUserPwd(${row.uid});">${trans('admin.changePassword')}</a></li>
                        ${adminOption}${bannedOption}
                    </ul>
                    </div>
                    ${deleteUserButton}`;
                }
            }
        ]
    });
}

function initPlayersTable() {
    let dataUrl = url('admin/player-data');
    if (getQueryString('uid')) {
        dataUrl += '?uid=' + getQueryString('uid');
    }
    $('#player-table').DataTable({
        ajax: dataUrl,
        scrollY: ($('.content-wrapper').height() - $('.content-header').outerHeight()) * 0.7,
        columnDefs: [
            {
                targets: 0,
                data: 'pid',
                width: '1%'
            },
            {
                targets: 1,
                data: 'uid',
                render: (data, type, row) => {
                    return `<span title="${trans('admin.doubleClickToSeeUser')}"
                    style="cursor: pointer;"
                    ondblclick="window.location.href = '${url('admin/users?uid=') + row.uid}'"
                    data-toggle="tooltip" data-placement="top">${data}</span>`;
                }
            },
            {
                targets: 2,
                data: 'player_name'
            },
            {
                targets: 3,
                data: 'preference',
                render: data => {
                    return `
                    <select class="form-control" onchange="changePreference.call(this)">
                        <option ${(data == "default") ? 'selected=selected' : ''} value="default">Default</option>
                        <option ${(data == "slim") ? 'selected=selected' : ''} value="slim">Slim</option>
                    </select>`;
                }
            },
            {
                targets: 4,
                searchable: false,
                orderable: false,
                render: (data, type, row) => {
                    let html = { steve: '', alex: '', cape: '' };
                    ['steve', 'alex', 'cape'].forEach(textureType => {
                        if (row['tid_' + textureType] === 0) {
                            html[textureType] = `<img id="${row.pid}-${row['tid_' + textureType]}" width="64" />`;
                        } else {
                            html[textureType] = `
                        <a href="${url('/')}skinlib/show/${row['tid_' + textureType]}">
                            <img id="${row.pid}-${row['tid_' + textureType]}" width="64" src="${url('/')}preview/64/${row['tid_' + textureType]}.png" />
                        </a>`;
                        }
                    });
                    return html.steve + html.alex + html.cape;
                }
            },
            {
                targets: 5,
                data: 'last_modified'
            },
            {
                targets: 6,
                searchable: false,
                orderable: false,
                render: (data, type, row) => {
                    return `
                    <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    ${trans('admin.operationsTitle')} <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:changeTexture(${row.pid}, '${row.player_name}');">${trans('admin.changeTexture')}</a></li>
                        <li><a href="javascript:changePlayerName(${row.pid}, '${row.player_name}');">${trans('admin.changePlayerName')}</a></li>
                        <li><a href="javascript:changeOwner(${row.pid});">${trans('admin.changeOwner')}</a></li>
                    </ul>
                    </div>
                    <a class="btn btn-danger btn-sm" href="javascript:deletePlayer(${row.pid});">${trans('admin.deletePlayer')}</a>`;
                }
            }
        ]
    });
}

function initPluginsTable() {
    return $('#plugin-table').DataTable({
        ajax: url('admin/plugins/data'),
        columnDefs: [
            {
                targets: 0,
                data: 'title'
            },
            {
                targets: 1,
                data: 'description',
                width: '35%'
            },
            {
                targets: 2,
                data: 'author',
                render: data => {
                    if (data.url === '' || data.url === null) {
                        return data.author;
                    } else {
                        return `<a href="${data.url}" target="_blank">${data.author}</a>`;
                    }
                }
            },
            {
                targets: 3,
                data: 'version'
            },
            {
                targets: 4,
                data: 'status'
            },
            {
                targets: 5,
                data: 'operations',
                searchable: false,
                orderable: false,
                render: (data, type, row) => {
                    let switchEnableButton, configViewButton, deletePluginButton;
                    if (data.enabled) {
                        switchEnableButton = `
                        <a class="btn btn-warning btn-sm" href="javascript:disablePlugin('${row.name}');">${trans('admin.disablePlugin')}</a>`;
                    } else {
                        switchEnableButton = `
                        <a class="btn btn-primary btn-sm" href="javascript:enablePlugin('${row.name}');">${trans('admin.enablePlugin')}</a>`;
                    }
                    if (data.enabled && data.hasConfigView) {
                        configViewButton = `
                        <a class="btn btn-default btn-sm" href="${url('/')}admin/plugins/config/${row.name}">${trans('admin.configurePlugin')}</a>`;
                    } else {
                        configViewButton = `
                        <a class="btn btn-default btn-sm" disabled="disabled" title="${trans('admin.noPluginConfigNotice')}" data-toggle="tooltip" data-placement="top">${trans('admin.configurePlugin')}</a>`;
                    }
                    deletePluginButton = `
                    <a class="btn btn-danger btn-sm" href="javascript:deletePlugin('${row.name}');">${trans('admin.deletePlugin')}</a>`;
                    return switchEnableButton + configViewButton + deletePluginButton;
                }
            }
        ]
    });
}
