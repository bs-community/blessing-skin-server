'use strict';

if ($('#user-table').length === 1) {
    $(document).ready(initUsersTable);
}

function initUsersTable() {
    const query = location.href.split('?')[1];

    $('#user-table').DataTable({
        columnDefs: usersTableColumnDefs,
        scrollY: ($('.content-wrapper').height() - $('.content-header').outerHeight()) * 0.7,
        fnDrawCallback: () => $('[data-toggle="tooltip"]').tooltip(),
        rowCallback: (row, data) => $(row).attr('id', `user-${data.uid}`),
        ajax: {
            url: url(`admin/user-data${ query ? ('?'+query) : '' }`),
            type: 'POST'
        }
    }).on('xhr.dt', handleDataTablesAjaxError);
}

const userPermissions = {
    '-1': 'banned',
    '0': 'normal',
    '1': 'admin',
    '2': 'superAdmin'
};

const usersTableColumnDefs = [
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
        data: 'nickname',
        render: $.fn.dataTable.render.text()
    },
    {
        targets: 3,
        data: 'score',
        render: data => `<input type="number" class="form-control score" value="${data}" title="${trans('admin.scoreTip')}" data-toggle="tooltip" data-placement="right">`
    },
    {
        targets: 4,
        data: 'players_count',
        searchable: false,
        orderable: false,
        render: (data, type, row) => `<a href="${url('admin/players?uid='+row.uid)}" title="${trans('admin.inspectHisPlayers')}" data-toggle="tooltip" data-placement="right">${data}</span>`
    },
    {
        targets: 5,
        data: 'permission',
        className: 'status',
        render: data => trans('admin.' + userPermissions[data])
    },
    {
        targets: 6,
        data: 'verified',
        className: 'verification',
        render: data => trans('admin.' + (data ? 'verified' : 'unverified'))
    },
    {
        targets: 7,
        data: 'register_at'
    },
    {
        targets: 8,
        data: 'operations',
        searchable: false,
        orderable: false,
        render: renderUsersTableOperations
    }
];

function renderUsersTableOperations(currentUserPermission, type, row) {
    let adminOption = '', bannedOption = '', deleteUserButton;

    if (row.permission !== 2) {
        // Only SUPER admins are allowed to set/unset admins
        if (currentUserPermission === 2) {
            const adminStatus = row.permission === 1 ? 'admin' : 'normal';
            adminOption = `<li class="divider"></li> <li><a id="admin-${row.uid}" data="${adminStatus}" onclick="changeAdminStatus(${row.uid});">
                ${ adminStatus === 'admin' ? trans('admin.unsetAdmin') : trans('admin.setAdmin') }
            </a></li>`;
        }

        const banStatus = row.permission === -1 ? 'banned' : 'normal';
        bannedOption = `<li class="divider"></li> <li><a id="ban-${row.uid}" data="${banStatus}" onclick="changeBanStatus(${row.uid});">
            ${  banStatus === 'banned' ? trans('admin.unban') : trans('admin.ban') }
        </a></li>`;
    }

    if (currentUserPermission === 2) {
        if (row.permission === 2) {
            deleteUserButton = `<a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="${trans('admin.cannotDeleteSuperAdmin')}">${trans('admin.deleteUser')}</a>`;
        } else {
            deleteUserButton = `<a class="btn btn-danger btn-sm" onclick="deleteUserAccount(${row.uid});">${trans('admin.deleteUser')}</a>`;
        }
    } else {
        if (row.permission === 1 || row.permission === 2) {
            deleteUserButton = `<a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="${trans('admin.cannotDeleteAdmin')}">${trans('admin.deleteUser')}</a>`;
        } else {
            deleteUserButton = `<a class="btn btn-danger btn-sm" onclick="deleteUserAccount(${row.uid});">${trans('admin.deleteUser')}</a>`;
        }
    }

    return `
    <div class="btn-group">
        <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            ${trans('admin.operationsTitle')} <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a onclick="changeUserEmail(${row.uid});">${trans('admin.changeEmail')}</a></li>
            <li><a onclick="changeUserVerification(${row.uid});">${trans('admin.changeVerification')}</a></li>
            <li><a onclick="changeUserNickName(${row.uid});">${trans('admin.changeNickName')}</a></li>
            <li><a onclick="changeUserPwd(${row.uid});">${trans('admin.changePassword')}</a></li>
            ${adminOption}
            ${bannedOption}
        </ul>
    </div>
    ${deleteUserButton}`;
}

async function changeUserEmail(uid) {
    const dom = $(`tr#user-${uid} > td:nth-child(2)`);
    let newUserEmail = '';

    try {
        newUserEmail = await swal({
            text: trans('admin.newUserEmail'),
            showCancelButton: true,
            input: 'text',
            inputValue: dom.text(),
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('auth.emptyEmail'));
            }))
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=email'),
            dataType: 'json',
            data: { uid: uid, email: newUserEmail }
        });

        if (errno === 0) {
            dom.text(newUserEmail);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeUserVerification(uid) {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=verification'),
            dataType: 'json',
            data: { uid: uid }
        });

        if (errno === 0) {
            const original = $(`#user-${uid} > td.verification`).text();

            $(`#user-${uid} > td.verification`).text(
                original === trans('admin.unverified') ? trans('admin.verified') : trans('admin.unverified')
            );

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeUserNickName(uid) {
    const dom = $(`tr#user-${uid} > td:nth-child(3)`);
    let newNickName = '';

    try {
        newNickName = await swal({
            text: trans('admin.newUserNickname'),
            showCancelButton: true,
            input: 'text',
            inputValue: dom.text(),
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('auth.emptyNickname'));
            }))
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=nickname'),
            dataType: 'json',
            data: { uid: uid, nickname: newNickName }
        });
        if (errno === 0) {
            dom.text(newNickName);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeUserPwd(uid) {
    let password;
    try {
        password = await swal({
            text: trans('admin.newUserPassword'),
            showCancelButton: true,
            input: 'password',
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=password'),
            dataType: 'json',
            data: { uid: uid, password: password }
        });
        errno === 0 ? toastr.success(msg) : toastr.warning(msg);
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeUserScore(uid, score) {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=score'),
            dataType: 'json',
            // Handle id formatted as '#user-1234'
            data: { uid: uid.slice(5), score: score }
        });
        errno === 0 ? toastr.success(msg) : toastr.warning(msg);
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeBanStatus(uid) {
    try {
        const { errno, msg, permission } = await fetch({
            type: 'POST',
            url: url('admin/users?action=ban'),
            dataType: 'json',
            data: { uid: uid }
        });

        if (errno === 0) {
            const dom = $(`#ban-${uid}`);

            if (dom.attr('data') === 'banned') {
                dom.text(trans('admin.ban')).attr('data', 'normal');
            } else {
                dom.text(trans('admin.unban')).attr('data', 'banned');
            }

            $(`#user-${uid} > td.status`).text(
                permission === -1 ? trans('admin.banned') : trans('admin.normal')
            );

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeAdminStatus(uid) {
    try {
        const { errno, msg, permission } = await fetch({
            type: 'POST',
            url: url('admin/users?action=admin'),
            dataType: 'json',
            data: { uid: uid }
        });

        if (errno === 0) {
            const dom = $(`#admin-${uid}`);

            if (dom.attr('data') === 'admin') {
                dom.text(trans('admin.setAdmin')).attr('data', 'normal');
            } else {
                dom.text(trans('admin.unsetAdmin')).attr('data', 'admin');
            }

            $(`#user-${uid} > td.status`).text(
                (permission === 1) ? trans('admin.admin') : trans('admin.normal')
            );

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function deleteUserAccount(uid) {
    try {
        await swal({
            text: trans('admin.deleteUserNotice'),
            type: 'warning',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/users?action=delete'),
            dataType: 'json',
            data: { uid: uid }
        });

        if (errno === 0) {
            $('tr#user-' + uid).remove();
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

$('body').on('keypress', '.score', function(event){
    // Change score when Enter key is pressed
    if (event.which === 13) {
        $(this).blur();
        changeUserScore($(this).parent().parent().attr('id'), $(this).val());
    }
});

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        initUsersTable,
        changeUserPwd,
        changeBanStatus,
        changeUserEmail,
        changeUserScore,
        changeAdminStatus,
        deleteUserAccount,
        changeUserNickName,
        changeUserVerification,
    };
}
