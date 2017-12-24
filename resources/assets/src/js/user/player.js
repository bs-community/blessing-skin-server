/* global MSP, defaultSkin */

'use strict';

$('body').on('click', '.player', function () {
    $('.player-selected').removeClass('player-selected');
    $(this).addClass('player-selected');

    showPlayerTexturePreview(this.id);
}).on('click', '#preview-switch', () => {
    TexturePreview.previewType == '3D' ? TexturePreview.show2dPreview() : TexturePreview.show3dPreview();
}).on('change', '#preference', async function () {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/player/preference'),
            dataType: 'json',
            data: { pid: $(this).attr('pid'), preference: $(this).val() }
        });
        errno == 0 ? toastr.success(msg) : toastr.warning(msg);
    } catch (error) {
        showAjaxError(error);
    }
});

async function showPlayerTexturePreview(pid) {
    try {
        const result = await fetch({
            type: 'GET',
            url: url('user/player/show'),
            dataType: 'json',
            data: { pid: pid }
        });

        // Render skin preview of selected player
        ['steve', 'alex', 'cape'].forEach((type) => {
            let tid     = result[`tid_${type}`];
            let preview = new TexturePreview(type, tid, result.preference);

            if (tid) {
                preview.change2dPreview().change3dPreview();
            } else {
                preview.showNotUploaded();
            }
        });

        if ((result.preference == 'default' && !result.tid_steve) ||
            (result.preference == 'slim' && !result.tid_alex))
        {
            // show default skin
            MSP.changeSkin(defaultSkin);
        }

        console.log(`Texture previews of player ${result.player_name} rendered.`);
    } catch (error) {
        showAjaxError(error);
    }
}

async function changePlayerName(pid) {
    let newPlayerName = '';
    const $playerName = $(`td:contains("${pid}")`).next();

    try {
        newPlayerName = await swal({
            title: trans('user.changePlayerName'),
            text: $('#player_name').attr('placeholder'),
            inputValue: $playerName.html(),
            input: 'text',
            showCancelButton: true,
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('skinlib.emptyPlayerName'));
            }))
        });
    } catch (error) {
        return;
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/player/rename'),
            dataType: 'json',
            data: { pid: pid, new_player_name: newPlayerName }
        });

        if (errno == 0) {
            swal({ type: 'success', html: msg });

            $playerName.html(newPlayerName);
        } else {
            swal({ type: 'warning', html: msg });
        }
    } catch (error) {
        showAjaxError(error);
    }
}

function clearTexture(pid) {
    let dom = `<div class="form-group">
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
    </script>`;

    return showModal(dom, trans('user.chooseClearTexture'), 'default', {
        callback: `ajaxClearTexture(${pid})`
    });
}

async function ajaxClearTexture(pid) {
    $('.modal').each(function () {
        if ($(this).css('display') == 'none') $(this).remove();
    });

    let data = { pid: pid };

    ['steve', 'alex', 'cape'].forEach(type => {
        data[type] = $(`#clear-${type}`).prop('checked') ? 1 : 0;
    });

    if (data['steve'] == 0 && data['alex'] == 0 && data['cape'] == 0) {
        return toastr.warning(trans('user.noClearChoice'));
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/player/texture/clear'),
            dataType: 'json',
            data: data
        });
        swal({ type: errno == 0 ? 'success' : 'error', html: msg });
        $('.modal').modal('hide');
    } catch (error) {
        showAjaxError(error);
    }
}

async function deletePlayer(pid) {
    try {
        await swal({
            title: trans('user.deletePlayer'),
            text: trans('user.deletePlayerNotice'),
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
            url: url('user/player/delete'),
            dataType: 'json',
            data: { pid: pid }
        });

        if (errno == 0) {
            await swal({
                type: 'success',
                html: msg
            });
            $(`tr#${pid}`).remove();
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function addNewPlayer() {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/player/add'),
            dataType: 'json',
            data: { player_name: $('#player_name').val() }
        });

        if (errno == 0) {
            swal({
                type: 'success',
                html: msg
            }).then(() => location.reload());

            $('#modal-add-player').modal('hide');
        } else {
            toastr.warning(msg);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        addNewPlayer,
        clearTexture,
        deletePlayer,
        changePlayerName,
        ajaxClearTexture,
    };
}
