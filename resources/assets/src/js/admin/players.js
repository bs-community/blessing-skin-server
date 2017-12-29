'use strict';

async function changePreference() {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/players?action=preference'),
            dataType: 'json',
            data: {
                pid: $(this).parent().parent().attr('id'),
                preference: $(this).val()
            }
        });
        errno === 0 ? toastr.success(msg) : toastr.warning(msg);
    } catch (error) {
        showAjaxError(error);
    }
}

function changeTexture(pid, playerName) {
    const dom   = `
    <div class="form-group">
        <label for="model">${trans('admin.textureType')}</label>
        <select class="form-control" id="model">
            <option value="steve">${trans('admin.skin', { 'model': 'Steve' })}</option>
            <option value="alex">${trans('admin.skin', { 'model': 'Alex' })}</option>
            <option value="cape">${trans('admin.cape')}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="tid">${trans('admin.pid')}</label>
        <input id="tid" class="form-control" type="text" placeholder="${trans('admin.pidNotice')}">
    </div>`;

    showModal(dom, trans('admin.changePlayerTexture', { 'player': playerName }), 'default', {
        callback: `ajaxChangeTexture(${pid})`
    });
    return;
}

async function ajaxChangeTexture(pid) {
    // Remove interference of modal which is hide
    $('.modal').each(function () {
        if ($(this).css('display') === 'none') $(this).remove();
    });

    const model = $('#model').val();
    const tid = $('#tid').val();

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/players?action=texture'),
            dataType: 'json',
            data: { pid: pid, model: model, tid: tid }
        });
        if (errno === 0) {
            $(`#${pid}-${model}`).attr('src', url(`preview/64/${tid}.png`));
            $('.modal').modal('hide');

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changePlayerName(pid, oldName) {
    const dom = $(`tr#${pid} > td:nth-child(3)`);
    let newPlayerName;

    try {
        newPlayerName = await swal({
            text: trans('admin.changePlayerNameNotice'),
            input: 'text',
            inputValue: oldName,
            inputValidator: name => (new Promise((resolve, reject) => {
                name ? resolve() : reject(trans('admin.emptyPlayerName'));
            }))
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/players?action=name'),
            dataType: 'json',
            data: { pid: pid, name: newPlayerName }
        });
        if (errno === 0) {
            dom.text(newPlayerName);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    } 
}

function changeOwner(pid) {
    const dom = $(`#${pid} > td:nth-child(2)`);
    let owner = 0;

    swal({
        html: `${trans('admin.changePlayerOwner')}<br><small>&nbsp;</small>`,
        input: 'number',
        inputValue: dom.text(),
        showCancelButton: true
    }).then(async uid => {
        owner = uid;

        try {
            const { errno, msg } = await fetch({
                type: 'POST',
                url: url('admin/players?action=owner'),
                dataType: 'json',
                data: { pid, uid }
            });
            if (errno === 0) {
                dom.text(owner);
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        } catch (error) {
            showAjaxError(error);
        }
    });

    $('.swal2-input').on('input', debounce(showNicknameInSwal, 350));
}

async function showNicknameInSwal() {
    const uid = $('.swal2-input').val();

    if (isNaN(uid) || uid <= 0)
        return;

    try {
        const { user } = await fetch({
            type: 'GET',
            url: url(`admin/user/${uid}`),
            dataType: 'json'
        });
        $('.swal2-content').html(
            trans('admin.changePlayerOwner') +
            '<small style="display: block; margin-top: .5em;">' +
            trans('admin.targetUser', { nickname: user.nickname }) +
            '</small>'
        );
    } catch (error) {
        $('.swal2-content').html(`
                    ${trans('admin.changePlayerOwner')}<br>
                    <small>${trans('admin.noSuchUser')}</small>
                `);
    }
}

async function deletePlayer(pid) {
    try {
        await swal({
            text: trans('admin.deletePlayerNotice'),
            type: 'warning',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('admin/players?action=delete'),
            dataType: 'json',
            data: { pid: pid }
        });
        if (errno === 0) {
            $(`tr#${pid}`).remove();
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        changeOwner,
        showNicknameInSwal,
        deletePlayer,
        changeTexture,
        changePlayerName,
        changePreference,
        ajaxChangeTexture,
    };
}
