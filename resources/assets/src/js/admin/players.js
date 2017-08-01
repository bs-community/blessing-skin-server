'use strict';

function changePreference() {
    fetch({
        type: 'POST',
        url: url('admin/players?action=preference'),
        dataType: 'json',
        data: {
            pid: $(this).parent().parent().attr('id'),
            preference: $(this).val()
        }
    }).then(({ errno, msg }) => {
        (errno == 0) ? toastr.success(msg) : toastr.warning(msg);
    }).catch(showAjaxError);
}

function changeTexture(pid, playerName) {
    let dom   = `
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

function ajaxChangeTexture(pid) {
    // Remove interference of modal which is hide
    $('.modal').each(function () {
        if ($(this).css('display') == 'none') $(this).remove();
    });

    var model = $('#model').val();
    var tid = $('#tid').val();

    fetch({
        type: 'POST',
        url: url('admin/players?action=texture'),
        dataType: 'json',
        data: { pid: pid, model: model, tid: tid }
    }).then(({ errno, msg }) => {
        if (errno == 0) {
            $(`#${pid}-${model}`).attr('src', url(`preview/64/${tid}.png`));
            $('.modal').modal('hide');

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function changePlayerName(pid, oldName) {
    let dom = $(`tr#${pid} > td:nth-child(3)`);
    let newPlayerName = '';

    swal({
        text: trans('admin.changePlayerNameNotice'),
        input: 'text',
        inputValue: oldName,
        inputValidator: name => (new Promise((resolve, reject) => {
            (newPlayerName = name) ? resolve() : reject(trans('admin.emptyPlayerName'));
        }))
    }).then(name => fetch({
        type: 'POST',
        url: url('admin/players?action=name'),
        dataType: 'json',
        data: { pid: pid, name: name }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            dom.text(newPlayerName);

            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function changeOwner(pid) {
    let dom = $(`#${pid} > td:nth-child(2)`);
    let owner = 0;

    swal({
        html: `${trans('admin.changePlayerOwner')}<br><small>&nbsp;</small>`,
        input: 'number',
        inputValue: dom.text(),
        showCancelButton: true
    }).then(uid => {
        owner = uid;

        return fetch({
            type: 'POST',
            url: url('admin/players?action=owner'),
            dataType: 'json',
            data: { pid: pid, uid: uid }
        });
    }).then(({ errno, msg }) => {
        if (errno == 0) {
            dom.text(owner);
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);

    $('.swal2-input').on('input', debounce(() => {
        let uid = $('.swal2-input').val();

        if (isNaN(uid) || uid <= 0)
            return;

        fetch({
            type: 'GET',
            url: url(`admin/user/${uid}`),
            dataType: 'json'
        }).then(result => {
            $('.swal2-content').html(
                trans('admin.changePlayerOwner') +
                '<small style="display: block; margin-top: .5em;">' +
                trans('admin.targetUser', { nickname: result.user.nickname }) +
                '</small>'
            );
        }).catch(() => {
            $('.swal2-content').html(`
                ${trans('admin.changePlayerOwner')}<br>
                <small>${trans('admin.noSuchUser')}</small>
            `);
        });
    }, 350));
}

function deletePlayer(pid) {
    swal({
        text: trans('admin.deletePlayerNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => fetch({
        type: 'POST',
        url: url('admin/players?action=delete'),
        dataType: 'json',
        data: { pid: pid }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            $(`tr#${pid}`).remove();
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        changeOwner,
        deletePlayer,
        changeTexture,
        changePlayerName,
        changePreference,
        ajaxChangeTexture,
    };
}
