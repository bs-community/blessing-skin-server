/* eslint no-unused-vars: "off" */

const $ = require('jquery');
window.$ = window.jQuery = $;

describe('tests for "closet" module', () => {
  const modulePath = '../user/closet';

  $.fn.jqPaginator = jest.fn();

  it('preview textures', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ type: 'skin', hash: 1 }))
      .mockReturnValueOnce(Promise.resolve({ type: 'cape', hash: 2 }))
      .mockReturnValueOnce(Promise.reject());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.MSP = MSP;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <div id="textures-indicator"></div>
      <div id="prev" class="item-selected">
        <div class="item-body item-selected"></div>
      </div>
      <div id="next" tid="1">
        <div class="item-body"></div>
      </div>
    `;
    require(modulePath);

    await $('#next > .item-body').click();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'skinlib/info/1',
      dataType: 'json'
    });
    expect($('#next').hasClass('item-selected')).toBe(true);
    expect(MSP.changeSkin).toBeCalledWith('textures/1');
    expect($('#textures-indicator').text()).toBe('general.skin');

    document.body.innerHTML = `
      <div id="textures-indicator"></div>
      <div id="prev" class="item-selected">
        <div class="item-body item-selected"></div>
      </div>
      <div id="next" tid="2">
        <div class="item-body"></div>
      </div>
    `;

    await $('#next > .item-body').click();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'skinlib/info/2',
      dataType: 'json'
    });
    expect($('#next').hasClass('item-selected')).toBe(true);
    expect(MSP.changeCape).toBeCalledWith('textures/2');
    expect($('#textures-indicator').text()).toBe('general.skin & general.cape');

    await $('#next > .item-body').click();
    expect(showAjaxError).toBeCalled();
  });

  it('render closet', () => {
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    window.trans = trans;
    window.url = url;
    window.showAjaxError = jest.fn();

    document.body.innerHTML = `
      <input name="q" />
      <div id="skin-category"></div>
      <div id="closet-paginator"></div>
    `;
    const renderCloset = require(modulePath).renderCloset;

    renderCloset([], 'skin');
    expect($('#closet-paginator').css('display')).toBe('none');
    expect(trans).toBeCalledWith('user.emptyClosetMsg', { url: 'skinlib?filter=skin' });
    expect($('#skin-category').html()).toBe(
      '<div class="empty-msg">user.emptyClosetMsg</div>'
    );

    $('input').val('q');
    renderCloset([], 'skin');
    expect($('#skin-category').html()).toBe(
      '<div class="empty-msg">general.noResult</div>'
    );

    renderCloset([{ tid: 1, name: 'name', type: 'steve' }], 'skin');
    expect($('#closet-paginator').css('display')).not.toBe('none');
    expect($('.item').attr('tid')).toBe('1');
    expect($('img').attr('src')).toBe('/preview/1.png');
    expect($('.texture-name').html().trim()).toBe(
      '<span title="name">name <small>(steve)</small></span>'
    );
    expect($('a.more').attr('href')).toBe('/skinlib/show/1');
    expect($('a.more').attr('title')).toBe('user.viewInSkinlib');
  });

  it('reload closet', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({
        items: [],
        category: 'skin',
        total_pages: 1
      }))
      .mockReturnValueOnce(Promise.reject());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <div id="skin-category">
        <div tid="1">
          <div class="item-footer">
            <div class="texture-name">
              <span></span>
            </div>
          </div>
        </div>
      </div>
      <div id="closet-paginator" last-skin-page="0"></div>
    `;
    const reloadCloset = require(modulePath).reloadCloset;

    await reloadCloset('skin', 1, 'q');
    expect(fetch).toBeCalledWith({
      type: 'GET',
      url: url('user/closet-data'),
      dataType: 'json',
      data: {
        category: 'skin',
        page: 1,
        perPage: 0,
        q: 'q'
      }
    });
    expect($('#closet-paginator').attr('last-skin-page')).toBe('1');

    await reloadCloset('skin', 1, 'q');
    expect(showAjaxError).toBeCalled();
  });

  it('calculate capacity of closet', () => {
    document.body.innerHTML = `
      <div id="skin-category" style="width: 900px">
        <div class="item" style="width: 50px; margin-right: 10.5px"></div>
      </div>
    `;

    const getCapacityOfCloset = require(modulePath).getCapacityOfCloset;
    expect(getCapacityOfCloset()).toBe(28);
  });

  it('rename item', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const swal = jest.fn()
      .mockImplementationOnce(() => Promise.reject())
      .mockImplementationOnce(({ inputValidator }) => {
        inputValidator('name');
        return Promise.resolve('name');
      });
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.swal = swal;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <div id="skin-category">
        <div tid="1">
          <div class="item-footer">
            <div class="texture-name">
              <span></span>
            </div>
          </div>
        </div>
      </div>
    `;
    const renameClosetItem = require(modulePath).renameClosetItem;

    await renameClosetItem(1, 'oldName');
    expect(fetch).not.toBeCalled();

    await renameClosetItem(1, 'oldName');
    expect(swal).toBeCalledWith(expect.objectContaining({
      title: trans('user.renameClosetItem'),
      input: 'text',
      inputValue: 'oldName',
      showCancelButton: true,
    }));
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/closet/rename',
      dataType: 'json',
      data: { tid: 1, new_name: 'name' }
    });
    expect(toastr.success).toBeCalledWith('success');
    expect($('span').html('name'));

    await renameClosetItem(1, 'oldName');
    expect(toastr.warning).toBeCalledWith('warning');

    await renameClosetItem(1, 'oldName');
    expect(showAjaxError).toBeCalled();
  });

  it('remove item from closet', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.swal = swal;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <div id="skin-category">
        <div id="shouldBeRemoved" tid="1"></div>
      </div>
    `;
    const removeFromCloset = require(modulePath).removeFromCloset;

    await removeFromCloset(1);
    expect(fetch).not.toBeCalled();

    await removeFromCloset(1);
    expect(swal).toBeCalledWith({
      text: 'user.removeFromClosetNotice',
      type: 'warning',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/closet/remove',
      dataType: 'json',
      data: { tid: 1 }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect(document.getElementById('shouldBeRemoved')).toBeNull();
    expect(trans).toBeCalledWith('user.emptyClosetMsg', { url: url('skinlib?filter=skin') });
    expect($('#skin-category').html()).toBe(
      '<div class="empty-msg">user.emptyClosetMsg</div>'
    );

    await removeFromCloset(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await removeFromCloset(1);
    expect(showAjaxError).toBeCalled();
  });

  it('set avatar', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.swal = swal;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <img alt="User Image" src="src" />
    `;
    const setAsAvatar = require(modulePath).setAsAvatar;

    await setAsAvatar(1);
    expect(fetch).not.toBeCalled();

    await setAsAvatar(1);
    expect(swal).toBeCalledWith({
      title: 'user.setAvatar',
      text: 'user.setAvatarNotice',
      type: 'question',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/profile/avatar',
      dataType: 'json',
      data: { tid: 1 }
    });
    expect(toastr.success).toBeCalledWith('success');
    expect($('img').attr('src').endsWith('src')).toBe(false);

    await setAsAvatar(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await setAsAvatar(1);
    expect(showAjaxError).toBeCalled();
  });
});

describe('tests for "player" module', () => {
  const modulePath = '../user/player';

  it('show player texture preview', async () => {
    const url = jest.fn(path => path);
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({
        tid_steve: 1,
        tid_alex: 2,
        tid_cape: 3,
        preference: 'default',
        player_name: 'name'
      }))
      .mockReturnValueOnce(Promise.reject());
    const showAjaxError = jest.fn();
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn(),
      setStatus: jest.fn(),
      getStatus: jest.fn()
    };
    window.url = url;
    window.fetch = fetch;
    window.showAjaxError = showAjaxError;
    window.TexturePreview = require('../common/texture-preview');
    window.MSP = MSP;
    window.defaultSkin = 'steve_base64';

    document.body.innerHTML = `
      <div id="1" class="player-selected player"></div>
      <div id="2" class="player"></div>
      <div id="preview-switch"></div>
    `;
    require(modulePath);

    await $('#2').click();
    expect($('#1').hasClass('player-selected')).toBe(false);
    expect($('#2').hasClass('player-selected')).toBe(true);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/player/show',
      dataType: 'json',
      data: { pid: '2' }
    });

    await $('#2').click();
    expect(showAjaxError).toBeCalled();

    $('#preview-switch').click();
    expect(window.TexturePreview.previewType).toBe('2D');
  });

  it('change player preference', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <select id="preference" pid="1">
          <option value="default" selected></option>
          <option value="slim"></option>
      </select>
    `;
    $('select').on('change', require(modulePath).changePreference);

    await $('select').val('slim').trigger('change');
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/player/preference',
      dataType: 'json',
      data: {
        pid: '1',
        preference: 'slim'
      }
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    await $('select').trigger('change');
    expect(toastr.warning).toBeCalledWith('warning');

    await $('select').trigger('change');
    expect(showAjaxError).toBeCalled();
  });

  it('change player name', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const trans = jest.fn(key => key);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const swal = jest.fn()
      .mockImplementationOnce(() => Promise.reject())
      .mockImplementationOnce(options => {
        options.inputValidator('name');
        return Promise.resolve('name');
      });
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.trans = trans;
    window.toastr = toastr;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <input id="player_name" placeholder="placeholder" />
      <table>
        <tbody>
          <td>1</td>
          <td id="player-name">old</td>
        </tbody>
      </table>
    `;
    const changePlayerName = require(modulePath).changePlayerName;

    await changePlayerName(1);
    expect(fetch).not.toBeCalled();

    await changePlayerName(1);
    expect(swal).toBeCalledWith(expect.objectContaining({
      title: 'user.changePlayerName',
      text: 'placeholder',
      inputValue: 'old',
      input: 'text',
      showCancelButton: true
    }));
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/player/rename',
      dataType: 'json',
      data: { pid: 1, new_player_name: 'name' }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect($('#player-name').html()).toBe('name');

    await changePlayerName(1);
    expect(swal).toBeCalledWith({ type: 'warning', html: 'warning' });

    await changePlayerName(1);
    expect(showAjaxError).toBeCalled();
  });

  it('show "clear texture" modal dialog', () => {
    const { clearTexture } = require(modulePath);
    const trans = jest.fn(key => key);
    const showModal = jest.fn();
    window.trans = trans;
    window.showModal = showModal;

    clearTexture();
    const args = showModal.mock.calls[0];
    expect(args.includes('user.chooseClearTexture')).toBe(true);
    expect(args.includes('default')).toBe(true);
  });

  it('submit clearing texture request', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const trans = jest.fn(key => key);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const showAjaxError = jest.fn();
    const modal = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.trans = trans;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;
    $.fn.modal = modal;

    document.body.innerHTML = `
      <div class="modal" id="shouldBeRemoved" style="display: none"></div>
      <div class="modal" id="shouldNotBeRemoved"></div>
      <input id="clear-steve">
      <input id="clear-alex">
      <input id="clear-cape">
    `;
    const ajaxClearTexture = require(modulePath).ajaxClearTexture;

    ajaxClearTexture(1);
    expect(document.getElementById('shouldBeRemoved')).toBeNull();
    expect(document.getElementById('shouldNotBeRemoved')).not.toBeNull();
    expect(toastr.warning).toBeCalledWith('user.noClearChoice');

    $('#clear-steve').prop('checked', true);
    await ajaxClearTexture(1);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/player/texture/clear',
      dataType: 'json',
      data: { pid: 1, steve: 1, alex: 0, cape: 0 }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect(modal).toBeCalledWith('hide');

    await ajaxClearTexture(1);
    expect(swal).lastCalledWith({ type: 'error', html: 'warning' });

    await ajaxClearTexture(1);
    expect(showAjaxError).toBeCalled();
  });

  it('delete player', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <tr id="1"></tr>
    `;
    const deletePlayer = require(modulePath).deletePlayer;

    await deletePlayer(1);
    expect(fetch).not.toBeCalled();

    await deletePlayer(1);
    expect(swal).toBeCalledWith({
      title: 'user.deletePlayer',
      text: 'user.deletePlayerNotice',
      type: 'warning',
      showCancelButton: true,
      cancelButtonColor: '#3085d6',
      confirmButtonColor: '#d33'
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/player/delete',
      dataType: 'json',
      data: { pid: 1 }
    });
    expect(swal).lastCalledWith({ type: 'success', html: 'success' });
    expect(document.getElementById('1')).toBeNull();

    await deletePlayer(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await deletePlayer(1);
    expect(showAjaxError).toBeCalled();
  });

  it('add a new player', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    const modal = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.swal = swal;
    window.showAjaxError = showAjaxError;
    $.fn.modal = modal;

    document.body.innerHTML = `
      <input id="player_name" value="name" />
    `;
    const addNewPlayer = require(modulePath).addNewPlayer;

    await addNewPlayer();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/player/add',
      dataType: 'json',
      data: { player_name: 'name' }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect(modal).toBeCalled();

    await addNewPlayer();
    expect(toastr.warning).toBeCalledWith('warning');

    await addNewPlayer();
    expect(showAjaxError).toBeCalled();
  });

  it('set texture', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn(),
      info: jest.fn()
    };
    const swal = jest.fn();
    const modal = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.swal = swal;
    $.fn.modal = modal;
    window.selectedTextures = {};
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <input name="player" id="1" />
    `;
    const setTexture = require(modulePath).setTexture;

    setTexture();
    expect(toastr.info).toBeCalledWith('user.emptySelectedPlayer');

    $('input').prop('checked', true);
    setTexture();
    expect(toastr.info).toBeCalledWith('user.emptySelectedTexture');

    window.selectedTextures = { skin: 1, cape: 2 };
    await setTexture();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/player/set',
      dataType: 'json',
      data: { 'pid': '1', 'tid[skin]': 1, 'tid[cape]': 2 }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect(modal).toBeCalledWith('hide');

    await setTexture();
    expect(toastr.warning).toBeCalledWith('warning');
    expect(modal.mock.calls.length).toBe(1);

    await setTexture();
    expect(showAjaxError).toBeCalled();
  });
});

describe('tests for "profile" module', () => {
  const modulePath = '../user/profile';

  it('change nickname', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.resolve())
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.swal = swal;
    window.trans = trans;
    window.url = url;
    window.debounce = jest.fn(fn => fn);
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <div class="nickname"></div>
      <input id="new-nickname" />
    `;
    const changeNickName = require(modulePath).changeNickName;

    await changeNickName();
    expect(swal).toBeCalledWith({ type: 'error', html: 'user.emptyNewNickName' });
    expect(fetch).not.toBeCalled();

    $('input').val('name');
    await changeNickName();
    expect(fetch).not.toBeCalled();

    await changeNickName();
    expect(trans).toBeCalledWith('user.changeNickName', { new_nickname: 'name' });
    expect(swal).toBeCalledWith({
      text: 'user.changeNickName',
      type: 'question',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/profile?action=nickname',
      dataType: 'json',
      data: { new_nickname: 'name' }
    });
    expect($('.nickname').text()).toBe('name');
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });

    await changeNickName();
    expect(swal).toBeCalled();

    await changeNickName();
    expect(showAjaxError).toBeCalled();
  });

  it('change password', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const toastr = {
      info: jest.fn(),
      warning: jest.fn()
    };
    const showAjaxError = jest.fn();
    const docCookies = {
      removeItem: jest.fn()
    };
    window.fetch = fetch;
    window.swal = swal;
    window.trans = trans;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;
    window.logout = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0 }))
      .mockReturnValueOnce(Promise.reject());
    window.docCookies = docCookies;

    document.body.innerHTML = `
      <input id="password" />
      <input id="new-passwd" />
      <input id="confirm-pwd" />
    `;
    const changePassword = require(modulePath).changePassword;

    changePassword();
    expect(toastr.info).toBeCalledWith('user.emptyPassword');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('password');
    changePassword();
    expect(toastr.info).toBeCalledWith('user.emptyNewPassword');
    expect($('#new-passwd').is(':focus')).toBe(true);

    $('#new-passwd').val('new-password');
    changePassword();
    expect(toastr.info).toBeCalledWith('auth.emptyConfirmPwd');
    expect($('#confirm-pwd').is(':focus')).toBe(true);

    $('#confirm-pwd').val('not-same').blur();
    changePassword();
    expect(toastr.warning).toBeCalledWith('auth.invalidConfirmPwd');
    expect($('#confirm-pwd').is(':focus')).toBe(true);

    $('#confirm-pwd').val('new-password');
    await changePassword();
    expect(swal).toBeCalledWith({ text: 'success', type: 'success' });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/profile?action=password',
      dataType: 'json',
      data: { current_password: 'password', new_password: 'new-password' }
    });
    expect(logout).toBeCalled();

    await changePassword();
    expect(docCookies.removeItem).toBeCalledWith('token');

    await changePassword();
    expect(swal).toBeCalledWith({ type: 'warning', text: 'warning' });

    await changePassword();
    expect(showAjaxError).toBeCalled();
  });

  it('change email', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.resolve())
      .mockReturnValueOnce(Promise.resolve())
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValue(Promise.resolve());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const toastr = {
      info: jest.fn(),
      warning: jest.fn()
    };
    const showAjaxError = jest.fn();
    const docCookies = {
      removeItem: jest.fn()
    };
    window.fetch = fetch;
    window.swal = swal;
    window.trans = trans;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;
    window.logout = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0 }))
      .mockReturnValueOnce(Promise.reject());
    window.docCookies = docCookies;

    document.body.innerHTML = `
      <input id="new-email" />
      <input id="current-password" value="pwd" />
    `;
    const changeEmail = require(modulePath).changeEmail;

    await changeEmail();
    expect(swal).toBeCalledWith({ type: 'error', html: 'user.emptyNewEmail' });
    expect(fetch).not.toBeCalled();

    $('#new-email').val('email');
    await changeEmail();
    expect(swal).toBeCalledWith({ type: 'warning', html: 'auth.invalidEmail' });

    $('#new-email').val('a@b.c');
    await changeEmail();    // Suppose the user cancelled changing email

    await changeEmail();
    expect(trans).toBeCalledWith('user.changeEmail', { new_email: 'a@b.c' });
    expect(swal).toBeCalledWith({
      text: 'user.changeEmail',
      type: 'question',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/profile?action=email',
      dataType: 'json',
      data: { new_email: 'a@b.c', password: 'pwd' }
    });
    expect(swal).toBeCalledWith({ type: 'success', text: 'success' });
    expect(logout).toBeCalled();

    await changeEmail();
    expect(docCookies.removeItem).toBeCalled();

    await changeEmail();
    expect(swal).toBeCalledWith({ type: 'warning', text: 'warning' });

    await changeEmail();
    expect(showAjaxError).toBeCalled();
  });

  it('delete account', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const toastr = {
      info: jest.fn(),
      warning: jest.fn()
    };
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.swal = swal;
    window.trans = trans;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <div class="modal-body">
        <input id="password" />
      </div>
    `;
    const deleteAccount = require(modulePath).deleteAccount;

    await deleteAccount();
    expect(swal).toBeCalledWith({ type: 'warning', html: 'user.emptyDeletePassword' });

    $('#password').val('password');
    await deleteAccount();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/profile?action=delete',
      dataType: 'json',
      data: { password: 'password' }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect(url).toBeCalledWith('auth/login');

    await deleteAccount();
    expect(swal).toBeCalledWith({ type: 'warning', html: 'warning' });

    await deleteAccount();
    expect(showAjaxError).toBeCalled();
  });
});

describe('tests for "sign" module', () => {
  const modulePath = '../user/sign';

  it('sign', async () => {
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    const showAjaxError = jest.fn();
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.showAjaxError = showAjaxError;
    window.debounce = fn => fn;
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({
        errno: 0,
        msg: 'success',
        score: 100,
        remaining_time: 0.1,
        storage: {
          used: 50,
          total: 100,
          percentage: 50
        }
      }))
      .mockReturnValueOnce(Promise.resolve({
        errno: 0,
        msg: 'success',
        score: 100,
        remaining_time: 24,
        storage: {
          used: 2000,
          total: 4000,
          percentage: 50
        }
      }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    window.fetch = fetch;

    document.body.innerHTML = `
      <div id="score"></div>
      <a id="sign-button"></a>
      <div id="user-storage"></div>
      <div id="user-storage-bar"></div>
    `;
    const sign = require(modulePath);

    await sign();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/sign',
      dataType: 'json'
    });
    expect($('#score').html()).toBe('100');
    expect(trans).toBeCalledWith(
      'user.signRemainingTime',
      { time: '6', unit: 'user.timeUnitMin' }
    );
    expect($('#sign-button').html()).toBe(
      '<i class="fa fa-calendar-check-o"></i> &nbsp;user.signRemainingTime'
    );
    expect($('#sign-button').attr('disabled')).toBe('disabled');
    expect($('#user-storage').html()).toBe('<b>50</b>/ 100 KB');
    expect($('#user-storage-bar').css('width')).toBe('50%');
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });

    await sign();
    expect(trans).toBeCalledWith(
      'user.signRemainingTime',
      { time: '24', unit: 'user.timeUnitHour' }
    );
    expect($('#user-storage').html()).toBe('<b>2</b>/ 4 MB');

    await sign();
    expect(toastr.warning).toBeCalledWith('warning');

    await sign();
    expect(showAjaxError).toBeCalled();
  });
});
