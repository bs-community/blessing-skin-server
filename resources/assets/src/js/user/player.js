/* global MSP, defaultSkin, selectedTextures */

'use strict';

$('body').on('click', '.player', function () {
    $('.player-selected').removeClass('player-selected');
    $(this).addClass('player-selected');

    showPlayerTexturePreview(this.id);
}).on('click', '#preview-switch', () => {
    TexturePreview.previewType == '3D' ? TexturePreview.show2dPreview() : TexturePreview.show3dPreview();
}).on('change', '#preference', function () {
    fetch({
        type: 'POST',
        url: url('user/player/preference'),
        dataType: 'json',
        data: { pid: $(this).attr('pid'), preference: $(this).val() }
    }).then(({ errno, msg }) => {
        (errno == 0) ? toastr.success(msg) : toastr.warning(msg);
    }).catch(showAjaxError);
});

function showPlayerTexturePreview(pid) {
    fetch({
        type: 'POST',
        url: url('user/player/show'),
        dataType: 'json',
        data: { pid: pid }
    }).then(result => {
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

    }).catch(showAjaxError);
}

function changePlayerName(pid) {
    let newPlayerName = '';
    const $playerName = $(`td:contains("${pid}")`).next();

    swal({
        title: trans('user.changePlayerName'),
        text: $('#player_name').attr('placeholder'),
        inputValue: $playerName.html(),
        input: 'text',
        showCancelButton: true,
        inputValidator: value => (new Promise((resolve, reject) => {
            (newPlayerName = value) ? resolve() : reject(trans('skinlib.emptyPlayerName'));
        }))
    }).then(name => fetch({
        type: 'POST',
        url: url('user/player/rename'),
        dataType: 'json',
        data: { pid: pid, new_player_name: name }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            swal({ type: 'success', html: msg });

            $playerName.html(newPlayerName);
        } else {
            swal({ type: 'error', html: msg });
        }
    }).catch(showAjaxError);
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

function ajaxClearTexture(pid) {
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

    fetch({
        type: 'POST',
        url: url('user/player/texture/clear'),
        dataType: 'json',
        data: data
    }).then(({ errno, msg }) => {
        swal({ type: errno == 0 ? 'success' : 'error', html: msg });
        $('.modal').modal('hide');
    }).catch(showAjaxError);
}

function deletePlayer(pid) {
    swal({
        title: trans('user.deletePlayer'),
        text: trans('user.deletePlayerNotice'),
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#3085d6',
        confirmButtonColor: '#d33'
    }).then(() => fetch({
        type: 'POST',
        url: url('user/player/delete'),
        dataType: 'json',
        data: { pid: pid }
    })).then(({ errno, msg }) => {
        if (errno == 0) {
            swal({
                type: 'success',
                html: msg
            }).then(() => $(`tr#${pid}`).remove());
        } else {
            swal({ type: 'error', html: msg });
        }
    }).catch(showAjaxError);
}

function addNewPlayer() {
    fetch({
        type: 'POST',
        url: url('user/player/add'),
        dataType: 'json',
        data: { player_name: $('#player_name').val() }
    }).then(({ errno, msg }) => {
        if (errno == 0) {
            swal({
                type: 'success',
                html: msg
            }).then(() => location.reload());

            $('#modal-add-player').modal('hide');
        } else {
            toastr.warning(msg);
        }
    }).catch(showAjaxError);
}

function setTexture() {
    let pid = 0,
        skin = selectedTextures['skin'],
        cape = selectedTextures['cape'];

    $('input[name="player"]').each(function(){
        if (this.checked) pid = this.id;
    });

    if (! pid) {
        toastr.info(trans('user.emptySelectedPlayer'));
    } else if (skin == undefined && cape == undefined) {
        toastr.info(trans('user.emptySelectedTexture'));
    } else {
        fetch({
            type: 'POST',
            url: url('user/player/set'),
            dataType: 'json',
            data: {
                'pid': pid,
                'tid[skin]': skin,
                'tid[cape]': cape
            }
        }).then(({ errno, msg }) => {
            if (errno == 0) {
                swal({ type: 'success', html: msg });
                $('#modal-use-as').modal('hide');
            } else {
                toastr.warning(msg);
            }
        }).catch(showAjaxError);
    }
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        changePlayerName,
        ajaxClearTexture,
        clearTexture,
        deletePlayer,
        addNewPlayer,
        setTexture
    };
}
