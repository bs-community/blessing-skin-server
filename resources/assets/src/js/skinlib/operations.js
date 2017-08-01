'use strict';

$(document).on('click', '.more.like', function () {
    let tid = $(this).attr('tid');

    if ($(this).hasClass('anonymous'))
        return;

    if ($(this).hasClass('liked')) {
        removeFromCloset(tid);
    } else {
        addToCloset(tid);
    }
});

function addToCloset(tid) {
    $.getJSON(url(`skinlib/info/${tid}`), ({ name }) => {
        swal({
            title: trans('skinlib.setItemName'),
            inputValue: name,
            input: 'text',
            showCancelButton: true,
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('skinlib.emptyItemName'));
            }))
        }).then(result => ajaxAddToCloset(tid, result));
    });
}

function ajaxAddToCloset(tid, name) {
    // Remove interference of modal which is hide
    $('.modal').each(function () {
        return ($(this).css('display') == 'none') ? $(this).remove() : null;
    });

    fetch({
        type: 'POST',
        url: url('user/closet/add'),
        dataType: 'json',
        data: { tid: tid, name: name }
    }).then(({ errno, msg }) => {
        if (errno == 0) {
            swal({ type: 'success', html: msg });

            $('.modal').modal('hide');
            updateTextureStatus(tid, 'add');
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function removeFromCloset(tid) {
    swal({
        text: trans('user.removeFromClosetNotice'),
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#3085d6',
        confirmButtonColor: '#d33'
    }).then(() => fetch({
        type: 'POST',
        url: url('/user/closet/remove'),
        dataType: 'json',
        data: { tid: tid }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            swal({ type: 'success', html: msg });

            updateTextureStatus(tid, 'remove');
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function changeTextureName(tid, oldName) {
    let newTextureName = '';

    swal({
        text: trans('skinlib.setNewTextureName'),
        input: 'text',
        inputValue: oldName,
        showCancelButton: true,
        inputValidator: value => (new Promise((resolve, reject) => {
            (newTextureName = value) ? resolve() : reject(trans('skinlib.emptyNewTextureName'));
        }))
    }).then(name => fetch({
        type: 'POST',
        url: url('skinlib/rename'),
        dataType: 'json',
        data: { tid: tid, new_name: name }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            $('#name').text(newTextureName);
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

/**
 * Update button action & likes of texture.
 *
 * @param  {number}         tid
 * @param  {'add'|'remove'} action
 * @return {null}
 */
function updateTextureStatus(tid, action) {
    let likes  = parseInt($('#likes').html()) + (action == 'add' ? 1 : -1);
        action = (action == 'add') ? 'removeFromCloset' : 'addToCloset';

    $(`a[tid=${tid}]`)
        .attr('href', `javascript:${action}(${tid});`)
        .attr('title', trans(`skinlib.${action}`))
        .toggleClass('liked');
    $(`#${tid}`)
        .attr('href', `javascript:${action}(${tid});`)
        .html(trans(`skinlib.${action}`));
    $('#likes').html(likes);
}

$(document).on('click', '.private-label', function () {
    swal({
        text: trans('skinlib.setPublicNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => {
        changePrivacy($(this).attr('tid'));
        $(this).remove();
    });
});

function changePrivacy(tid) {
    fetch({
        type: 'POST',
        url: url('skinlib/privacy'),
        dataType: 'json',
        data: { tid: tid }
    }).then(result => {
        let { errno, msg } = result;

        if (errno == 0) {
            toastr.success(msg);

            if (result.public == '0') {
                $(`a:contains("${trans('skinlib.setAsPrivate')}")`).html(trans('skinlib.setAsPublic'));
            } else {
                $(`a:contains("${trans('skinlib.setAsPublic')}")`).html(trans('skinlib.setAsPrivate'));
            }
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function deleteTexture(tid) {
    swal({
        text: trans('skinlib.deleteNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => fetch({
        type: 'POST',
        url: url('skinlib/delete'),
        dataType: 'json',
        data: { tid: tid }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            swal({ type: 'success', html: msg }).then(() => {
                window.location = url('skinlib');
            });
        } else {
            swal({ type: 'warning', html: msg });
        }
    }).catch(showAjaxError);
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        addToCloset,
        ajaxAddToCloset,
        removeFromCloset,
        changeTextureName,
        updateTextureStatus,
        changePrivacy,
        deleteTexture
    };
}
