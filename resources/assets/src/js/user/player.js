/* global initSkinViewer, defaultSteveSkin, defaultAlexSkin */

'use strict';

$('body').on('click', '.player', function () {
    $('.player-selected').removeClass('player-selected');
    $(this).addClass('player-selected');

    showPlayerTexturePreview(this.id);
}).on('change', '#preference', async function () {
    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/player/preference'),
            dataType: 'json',
            data: { pid: $(this).attr('pid'), preference: $(this).val() }
        });
        errno === 0 ? toastr.success(msg) : toastr.warning(msg);
    } catch (error) {
        showAjaxError(error);
    }
}).on('click', '#preview-switch', () => {
    // Switch preview type between 2D and 3D
    $('#preview-3d-container').toggle();
    $('#preview-2d-container').toggle();
    $('.operations').toggle();

    if ($('#preview-3d-container').is(':visible')) {
        $('#preview-switch').html(trans('user.switch2dPreview'));
    } else {
        $('#preview-switch').html(trans('user.switch3dPreview'));
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

        let shouldBeUpdated = false;

        for (const type of ['steve', 'alex', 'cape']) {
            // Render skin preview of selected player
            const tid = result[`tid_${type}`];

            if (tid) {
                $(`#${type}`)
                    .attr('src', url(`preview/200/${tid}.png`)).show().parent()
                    .attr('href', url(`skinlib/show/${tid}`)).next().hide();

                const { hash } = await fetch({
                    type: 'GET',
                    url: url(`skinlib/info/${tid}`),
                    dataType: 'json'
                });

                if (type === 'cape') {
                    $.msp.config.capeUrl = url(`textures/${hash}`);
                } else if (type === (result.preference === 'slim' ? 'alex' : 'steve')) {
                    $.msp.config.skinUrl = url(`textures/${hash}`);
                }
            } else {
                $(`#${type}`).hide().parent().next().show();

                if (type === 'cape') {
                    $.msp.config.capeUrl = '';
                } else if (type === (result.preference === 'slim' ? 'alex' : 'steve')) {
                    $.msp.config.skinUrl = type === 'steve' ? defaultSteveSkin : defaultAlexSkin;
                }
            }
        }

        if ($.msp.config.slim !== (result.preference === 'slim')) {
            $.msp.config.slim = !$.msp.config.slim;
            shouldBeUpdated = true;
        }

        if ($.msp.config.skinUrl !== $.msp.viewer.skinUrl || $.msp.config.capeUrl !== $.msp.viewer.capeUrl) {
            shouldBeUpdated = true;
        }

        if (shouldBeUpdated) {
            initSkinViewer();
            console.log(`[skinview3d] texture previews of player ${result.player_name} rendered`);
        }
    } catch (error) {
        showAjaxError(error);
    }
}

async function changePlayerName(pid) {
    let newPlayerName = '';
    const $playerName = $(`tr#${pid} td.player-name`);

    try {
        newPlayerName = await swal({
            title: trans('user.changePlayerName'),
            text: $('#player_name').attr('placeholder'),
            inputValue: $playerName.html(),
            input: 'text',
            showCancelButton: true,
            inputValidator: value => (new Promise((resolve, reject) => {
                value ? resolve() : reject(trans('user.emptyPlayerName'));
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

        if (errno === 0) {
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
    const dom = `<div class="form-group">
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
        if ($(this).css('display') === 'none') $(this).remove();
    });

    const data = { pid: pid };

    ['steve', 'alex', 'cape'].forEach(type => {
        data[type] = $(`#clear-${type}`).prop('checked') ? 1 : 0;
    });

    if (data['steve'] === 0 && data['alex'] === 0 && data['cape'] === 0) {
        return toastr.warning(trans('user.noClearChoice'));
    }

    try {
        const { errno, msg } = await fetch({
            type: 'POST',
            url: url('user/player/texture/clear'),
            dataType: 'json',
            data: data
        });
        swal({ type: errno === 0 ? 'success' : 'error', html: msg });
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

        if (errno === 0) {
            await swal({
                type: 'success',
                html: msg
            });
            $(`tr#${pid}`).remove();
        } else {
            swal({ type: 'warning', html: msg });
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

        if (errno === 0) {
            await swal({ type: 'success', html: msg });

            $('#modal-add-player').modal('hide');
            location.reload();
        } else {
            swal({ type: 'warning', html: msg });
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
