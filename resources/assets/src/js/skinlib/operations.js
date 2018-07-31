'use strict';

$(document).on('click', '.more.like', toggleLiked);

function toggleLiked() {
    const tid = $(this).attr('tid');

    if ($(this).hasClass('anonymous'))
        return;

    if ($(this).hasClass('liked')) {
        removeFromCloset(tid);
    } else {
        addToCloset(tid);
    }
}

function addToCloset(tid) {
    $.getJSON(url(`skinlib/info/${tid}`), async ({ name }) => {
        try {
            const result = await swal({
                title: trans('skinlib.setItemName'),
                text: trans('skinlib.applyNotice'),
                inputValue: name,
                input: 'text',
                showCancelButton: true,
                inputValidator: value => (new Promise((resolve, reject) => {
                    value ? resolve() : reject(trans('skinlib.emptyItemName'));
                }))
            });
            ajaxAddToCloset(tid, result);
        } catch (error) {
            //
        }
    });
}

async function ajaxAddToCloset(tid, name) {
    // Remove interference of modal which is hide
    $('.modal').each(function () {
        return ($(this).css('display') === 'none') ? $(this).remove() : null;
    });

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/closet/add'),
            dataType: 'json',
            data: { tid: tid, name: name }
        });

        if (errno === 0) {
            swal({ type: 'success', html: msg });

            $('.modal').modal('hide');
            updateTextureStatus(tid, 'add');
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function removeFromCloset(tid) {
    try {
        await swal({
            text: trans('user.removeFromClosetNotice'),
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#3085d6',
            confirmButtonColor: '#d33'
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('/user/closet/remove'),
            dataType: 'json',
            data: { tid: tid }
        });

        if (errno === 0) {
            swal({ type: 'success', html: msg });

            updateTextureStatus(tid, 'remove');
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeTextureName(tid, oldName) {
    let newTextureName = '';

    try {
        newTextureName = await swal({
            text: trans('skinlib.setNewTextureName'),
            input: 'text',
            inputValue: oldName,
            showCancelButton: true,
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('skinlib.emptyNewTextureName'));
            }))
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('skinlib/rename'),
            dataType: 'json',
            data: { tid: tid, new_name: newTextureName }
        });

        if (errno === 0) {
            $('#name').text(newTextureName);
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changeTextureModel(tid, oldModel) {
    const models = {
        steve: 'steve',
        alex: 'alex',
        cape: trans('general.cape')
    };

    let newTextureModel = '';

    try {
        newTextureModel = await swal({
            text: trans('skinlib.setNewTextureModel'),
            input: 'select',
            inputValue: oldModel,
            inputOptions: models,
            showCancelButton: true,
            inputClass: 'form-control'
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('skinlib/model'),
            dataType: 'json',
            data: { tid: tid, model: newTextureModel }
        });

        if (errno === 0) {
            $('#model').text(models[newTextureModel]);
            toastr.success(msg);
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

/**
 * Update button action & likes of texture.
 *
 * @param  {number}         tid
 * @param  {'add'|'remove'} action
 * @return {null}
 */
function updateTextureStatus(tid, action) {
    const likes  = parseInt($('#likes').html()) + (action === 'add' ? 1 : -1);
    const buttonAction = (action === 'add') ? 'removeFromCloset' : 'addToCloset';

    $(`a[tid=${tid}]`)
        .attr('onclick', `${buttonAction}(${tid});`)
        .attr('title', trans(`skinlib.${buttonAction}`))
        .toggleClass('liked');
    $(`#${tid}`)
        .attr('onclick', `${buttonAction}(${tid});`)
        .html(trans(`skinlib.${buttonAction}`));
    $('#likes').html(likes);
    $('#quick-apply').toggle(action === 'add');
}

$(document).on('click', '.private-label', async function () {
    try {
        await swal({
            text: trans('skinlib.setPublicNotice'),
            type: 'warning',
            showCancelButton: true
        });

        changePrivacy($(this).attr('tid'));
        $(this).remove();
    } catch (error) {
        //
    }
});

async function changePrivacy(tid) {
    try {
        const result = await fetch({
            type: 'POST',
            url: url('skinlib/privacy'),
            dataType: 'json',
            data: { tid: tid }
        });
        const { errno, msg } = result;

        if (errno === 0) {
            toastr.success(msg);

            if (result.public === '0') {
                $(`a:contains("${trans('skinlib.setAsPrivate')}")`).html(trans('skinlib.setAsPublic'));
            } else {
                $(`a:contains("${trans('skinlib.setAsPublic')}")`).html(trans('skinlib.setAsPrivate'));
            }
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function deleteTexture(tid) {
    try {
        await swal({
            text: trans('skinlib.deleteNotice'),
            type: 'warning',
            showCancelButton: true
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('skinlib/delete'),
            dataType: 'json',
            data: { tid: tid }
        });

        if (errno === 0) {
            await swal({ type: 'success', html: msg });
            window.location = url('skinlib');
        } else {
            swal({ type: 'warning', html: msg });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        toggleLiked,
        addToCloset,
        changePrivacy,
        deleteTexture,
        ajaxAddToCloset,
        removeFromCloset,
        changeTextureName,
        changeTextureModel,
        updateTextureStatus,
    };
}
