/* eslint no-unused-vars: "off" */

const $ = require('jquery');
window.$ = window.jQuery = $;

jest.useFakeTimers();

describe('tests for "customize" module', () => {
  const modulePath = '../admin/customize';

  it('change skin preview after switching color', () => {
    window.current_skin = 'skin-blue';
    window.showAjaxError = jest.fn();
    document.body.className = window.current_skin;
    document.body.innerHTML = `
      <div id="layout-skins-list">
      <a data-skin="skin-purple"></a>
      </div>`;

    require(modulePath);
    $('#layout-skins-list [data-skin]').click();

    expect($('body').hasClass('skin-blue')).toBe(false);
    expect($('body').hasClass('skin-purple')).toBe(true);
    expect(window.current_skin).toBe('skin-purple');
  });

  it('submit information of skin', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject(new Error));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.current_skin = '';
    window.showAjaxError = jest.fn();

    document.body.innerHTML = '<button id="color-submit">submit</button>';
    const submitColor = require(modulePath);

    await submitColor();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/customize?action=color',
      dataType: 'json',
      data: { color_scheme: '' }
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    await submitColor();
    expect(toastr.warning).toBeCalledWith('warning');

    await submitColor();
    expect(window.showAjaxError).toBeCalled();
  });
});

describe('tests for "players" module', () => {
  const modulePath = '../admin/players';

  it('show "change player texture" modal dialog', () => {
    const trans = jest.fn(key => key);
    const showModal = jest.fn();
    window.trans = trans;
    window.showModal = showModal;

    const changeTexture = require(modulePath).changeTexture;
    changeTexture(1, 'name');
    const args = showModal.mock.calls[0];
    expect(args.includes('admin.changePlayerTexture')).toBe(true);
    expect(args.includes('default')).toBe(true);
  });

  it('change player preference', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject(new Error));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = jest.fn();

    document.body.innerHTML = `
      <div id="1">
        <div><select>
          <option value="default" selected></option>
          <option value="slim"></option>
        </select></div>
      </div>
    `;
    $('select').on('change', require(modulePath).changePreference);

    await $('select').val('slim').trigger('change');
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/players?action=preference',
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
    expect(window.showAjaxError).toBeCalled();
  });

  it('submit changed texture information', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const modal = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.$.fn.modal = modal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <div class="modal" style="display: none" id="shouldBeRemoved"></div>
      <div class="modal" id="shouldNotBeRemoved"></div>
      <input id="model" value="default" />
      <input id="tid" value="1" />
      <img id="1-default" src="" />
    `;
    const ajaxChangeTexture = require(modulePath).ajaxChangeTexture;

    await ajaxChangeTexture(1);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/players?action=texture',
      dataType: 'json',
      data: { pid: 1, model: 'default', tid: '1' }
    });
    expect(document.getElementById('shouldBeRemoved')).toBeNull();
    expect(document.getElementById('shouldNotBeRemoved')).not.toBeNull();
    expect(modal).toBeCalledWith('hide');
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect($('img').attr('src')).toBe('preview/64/1.png');

    await ajaxChangeTexture(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await ajaxChangeTexture(1);
    expect(showAjaxError).toBeCalled();
  });

  it('change player name', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn()
      .mockImplementationOnce(() => Promise.reject())
      .mockImplementationOnce(options => {
        options.inputValidator('newName');
        return Promise.resolve('newName');
      });
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <table>
        <thead></thead>
        <tbody>
        <tr id="1">
          <td></td>
          <td></td>
          <td></td>
        </tr>
        </tbody>
      </table>
    `;
    const changePlayerName = require(modulePath).changePlayerName;

    await changePlayerName(1, 'oldName');
    expect(fetch).not.toBeCalled();

    await changePlayerName(1, 'oldName');
    expect(swal).toBeCalledWith(expect.objectContaining({
      text: 'admin.changePlayerNameNotice',
      input: 'text',
      inputValue: 'oldName'
    }));
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/players?action=name',
      dataType: 'json',
      data: { pid: 1, name: 'newName' }
    });
    expect($('tr#1 > td:nth-child(3)').text()).toBe('newName');
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    await changePlayerName(1, 'oldName');
    expect(toastr.warning).toBeCalledWith('warning');

    await changePlayerName(1, 'oldName');
    expect(showAjaxError).toBeCalled();
  });

  it('change owner', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject(new Error));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn().mockReturnValue(Promise.resolve(2));
    const debounce = jest.fn(fn => fn);
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.debounce = debounce;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <table>
        <thead></thead>
        <tbody>
        <tr id="1">
          <td></td>
          <td>1</td>
          <td></td>
        </tr>
        </tbody>
      </table>
      <div>
        <input class="swal2-input" />
        <div class="swal2-content"></div>
      </div>
    `;
    const changeOwner = require(modulePath).changeOwner;

    await changeOwner(1, 'oldName');
    expect(debounce).toBeCalled();
    expect(swal).toBeCalledWith({
      html: 'admin.changePlayerOwner<br><small>&nbsp;</small>',
      input: 'number',
      inputValue: '1',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/players?action=owner',
      dataType: 'json',
      data: { pid: 1, uid: 2 }
    });
    await changeOwner(1, 'oldName');
    expect($('tr#1 > td:nth-child(2)').text()).toBe('2');
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    await changeOwner(1, 'oldName');
    expect(toastr.warning).toBeCalledWith('warning');

    await changeOwner(1, 'oldName');
    expect(showAjaxError).toBeCalled();
  });

  it('show nickname in swal', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ user: { nickname: 'name' } }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const trans = jest.fn(key => key);
    window.fetch = fetch;
    window.url = url;
    window.trans = trans;

    document.body.innerHTML = `
      <input class="swal2-input" />
      <div class="swal2-content"></div>
    `;
    const { showNicknameInSwal } = require(modulePath);

    await showNicknameInSwal();
    expect(fetch).not.toBeCalled();

    $('input').val('-8');
    await showNicknameInSwal();
    expect(fetch).not.toBeCalled();

    $('input').val('8');
    await showNicknameInSwal();
    expect(fetch).toBeCalledWith({
      type: 'GET',
      url: 'admin/user/8',
      dataType: 'json'
    });
    expect($('div').html().includes('name'));

    await showNicknameInSwal();
    expect($('div').html().includes('admin.noSuchUser'));
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
    const trans = jest.fn(key => key);
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <table>
        <thead></thead>
        <tbody>
        <tr id="1">
        </tr>
        </tbody>
      </table>
    `;
    const deletePlayer = require(modulePath).deletePlayer;

    await deletePlayer(1);
    expect(fetch).not.toBeCalled();

    await deletePlayer(1);
    expect(swal).toBeCalledWith({
      text: 'admin.deletePlayerNotice',
      type: 'warning',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/players?action=delete',
      dataType: 'json',
      data: { pid: 1 }
    });
    expect(document.getElementById('1')).toBeNull();
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    await deletePlayer(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await deletePlayer(1);
    expect(showAjaxError).toBeCalled();
  });
});

describe('tests for "plugins" module', () => {
  const modulePath = '../admin/plugins';

  it('enable a plugin', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const reloadTable = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;
    $.pluginsTable = {
      ajax: {
        reload: reloadTable
      }
    };

    const enablePlugin = require(modulePath).enablePlugin;

    await enablePlugin('plugin');
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/plugins/manage?action=enable&name=plugin',
      dataType: 'json'
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect(reloadTable).toBeCalledWith(null, false);

    await enablePlugin('plugin');
    expect(toastr.warning).toBeCalledWith('warning');

    await enablePlugin('plugin');
    expect(showAjaxError).toBeCalled();
  });

  it('disable a plugin', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const reloadTable = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;
    $.pluginsTable = {
      ajax: {
        reload: reloadTable
      }
    };

    const disablePlugin = require(modulePath).disablePlugin;

    await disablePlugin('plugin');
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/plugins/manage?action=disable&name=plugin',
      dataType: 'json'
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect(reloadTable).toBeCalledWith(null, false);

    await disablePlugin('plugin');
    expect(toastr.warning).toBeCalledWith('warning');

    await disablePlugin('plugin');
    expect(showAjaxError).toBeCalled();
  });

  it('delete a plugin', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const reloadTable = jest.fn();
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.showAjaxError = showAjaxError;
    $.pluginsTable = {
      ajax: {
        reload: reloadTable
      }
    };
    window.swal = swal;

    const deletePlugin = require(modulePath).deletePlugin;

    await deletePlugin('plugin');
    expect(fetch).not.toBeCalled();

    await deletePlugin('plugin');
    expect(swal).toBeCalledWith({
      text: 'admin.confirmDeletion',
      type: 'warning',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/plugins/manage?action=delete&name=plugin',
      dataType: 'json'
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect(reloadTable).toBeCalledWith(null, false);

    await deletePlugin('plugin');
    expect(toastr.warning).toBeCalledWith('warning');

    await deletePlugin('plugin');
    expect(showAjaxError).toBeCalled();
  });
});

describe('tests for "update" module', () => {
  const modulePath = '../admin/update';

  it('download updates', async () => {
    const fetch = jest.fn()
      .mockImplementationOnce(({ beforeSend }) => {
        beforeSend && beforeSend();
        return Promise.resolve({
          file_size: 5000
        });
      })
      .mockImplementationOnce(() => Promise.resolve())
      .mockImplementationOnce(() => Promise.resolve({ msg: 'ok' }))
      .mockImplementationOnce(() => Promise.reject())
      .mockImplementationOnce(({ beforeSend }) => {
        beforeSend && beforeSend();
        return Promise.resolve({
          file_size: 5000
        });
      })
      .mockImplementationOnce(() => Promise.resolve())
      .mockImplementationOnce(() => Promise.resolve({ msg: 'ok' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const modal = jest.fn();
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.resolve())
      .mockReturnValueOnce(Promise.reject());
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.swal = swal;
    window.showAjaxError = jest.fn();
    $.fn.modal = modal;

    document.body.innerHTML = `
      <div id="file-size"></div>
      <div id="modal-start-download"></div>
      <button id="update-button"></button>
      <div class="modal-title"></div>
      <div class="modal-body"></div>
    `;

    const downloadUpdates = require(modulePath).downloadUpdates;

    await downloadUpdates();
    expect(fetch).toBeCalledWith(expect.objectContaining({
      url: 'admin/update/download?action=prepare-download',
      type: 'GET',
      dataType: 'json',
    }));
    expect($('#update-button').prop('disabled')).toBe(true);
    expect($('#file-size').html()).toBe('5000');
    expect(modal).toBeCalledWith({
      backdrop: 'static',
      keyboard: false
    });
    expect(fetch).toBeCalledWith({
      url: 'admin/update/download?action=start-download',
      type: 'POST',
      dataType: 'json'
    });
    expect($('.modal-title').html().includes('admin.extracting')).toBe(true);
    expect($('.modal-body').html().includes('admin.downloadCompleted')).toBe(true);
    expect(swal).toBeCalledWith({ type: 'success', html: 'ok' });
    expect(url).toBeCalledWith('/');

    await downloadUpdates();
    expect(window.showAjaxError).toBeCalled();

    await downloadUpdates();
    expect(url).toBeCalledWith('/');
  });

  it('download progress polling', async () => {
    const fetch = jest.fn().mockReturnValueOnce(Promise.resolve({ size: 50 }));
    const url = jest.fn(path => path);
    window.fetch = fetch;
    window.url = url;

    document.body.innerHTML = `
      <div id="imported-progress"></div>
      <div class="progress-bar"></div>
    `;

    const { progressPolling } = require(modulePath);
    await progressPolling(100)();
    expect(fetch).toBeCalledWith({
      url: 'admin/update/download?action=get-file-size',
      type: 'GET'
    });
    expect($('#imported-progress').html()).toBe('50.00');
    expect($('.progress-bar').css('width')).toBe('50%');
    expect($('.progress-bar').attr('aria-valuenow')).toBe('50.00');
  });

  it('check for updates', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({
        available: false,
        latest: '1.1.4'
      }))
      .mockReturnValueOnce(Promise.resolve({
        available: true,
        latest: '5.1.4'
      }));
    const url = jest.fn(path => path);

    window.fetch = fetch;
    window.url = url;

    document.body.innerHTML = `
      <a id="target" href="admin/update"><i class="fa fa-arrow-up"></i> <span>Check Update</span></a>
    `;

    const checkForUpdates = require(modulePath).checkForUpdates;

    await checkForUpdates();
    expect($('#target').html()).toBe(
      '<i class="fa fa-arrow-up"></i> <span>Check Update</span>'
    );

    await checkForUpdates();
    expect($('#target').html()).toBe(
      '<i class="fa fa-arrow-up"></i> <span>Check Update</span>'+
      '<span class="label label-primary pull-right">v5.1.4</span>'
    );
  });
});

describe('tests for "users" module', () => {
  const modulePath = '../admin/users';

  it('change user email', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn()
      .mockImplementationOnce(() => Promise.reject())
      .mockImplementationOnce(options => {
        options.inputValidator('a@b.c');
        return Promise.resolve('a@b.c');
      });
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <table>
        <tbody>
          <tr id="user-1">
            <td></td>
            <td>d@e.f</td>
          </tr>
        </tbody>
      </table>
    `;
    const changeUserEmail = require(modulePath).changeUserEmail;

    await changeUserEmail(1);
    expect(fetch).not.toBeCalled();

    await changeUserEmail(1);
    expect(swal).toBeCalledWith(expect.objectContaining({
      text: 'admin.newUserEmail',
      showCancelButton: true,
      input: 'text',
      inputValue: 'd@e.f'
    }));
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=email',
      dataType: 'json',
      data: { uid: 1, email: 'a@b.c' }
    });
    expect($('tr > td:nth-child(2)').text()).toBe('a@b.c');
    expect(toastr.success).toBeCalledWith('success');

    await changeUserEmail(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await changeUserEmail(1);
    expect(showAjaxError).toBeCalled();
  });

  it('change user nick name', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn()
      .mockImplementationOnce(() => Promise.reject())
      .mockImplementationOnce(options => {
        options.inputValidator('foo');
        return Promise.resolve('foo');
      });
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <table>
        <tbody>
          <tr id="user-1">
            <td></td>
            <td></td>
            <td>hhh</td>
          </tr>
        </tbody>
      </table>
    `;
    const changeUserNickName = require(modulePath).changeUserNickName;

    await changeUserNickName(1);
    expect(fetch).not.toBeCalled();

    await changeUserNickName(1);
    expect(swal).toBeCalledWith(expect.objectContaining({
      text: 'admin.newUserNickname',
      showCancelButton: true,
      input: 'text',
      inputValue: 'hhh'
    }));
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=nickname',
      dataType: 'json',
      data: { uid: 1, nickname: 'foo' }
    });
    expect($('tr > td:nth-child(3)').text()).toBe('foo');
    expect(toastr.success).toBeCalledWith('success');

    await changeUserNickName(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await changeUserNickName(1);
    expect(showAjaxError).toBeCalled();
  });

  it('change user password', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve('secret'));
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    const changeUserPwd = require(modulePath).changeUserPwd;

    await changeUserPwd(1);
    expect(fetch).not.toBeCalled();

    await changeUserPwd(1);
    expect(swal).toBeCalledWith(expect.objectContaining({
      text: 'admin.newUserPassword',
      showCancelButton: true,
      input: 'password',
    }));
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=password',
      dataType: 'json',
      data: { uid: 1, password: 'secret' }
    });
    expect(toastr.success).toBeCalledWith('success');

    await changeUserPwd(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await changeUserPwd(1);
    expect(showAjaxError).toBeCalled();
  });

  it('change user score', async () => {
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
      <table>
        <tbody>
          <tr id="user-1">
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    `;
    const changeUserScore = require(modulePath).changeUserScore;

    await changeUserScore('user-1', 50);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=score',
      dataType: 'json',
      data: { uid: '1', score: 50 }
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    await changeUserScore('user-1', 50);
    expect(toastr.warning).toBeCalledWith('warning');

    await changeUserScore('user-1', 50);
    expect(showAjaxError).toBeCalled();
  });

  it('change ban status', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({
        errno: 0,
        msg: 'success',
        permission: 0
      }))
      .mockReturnValueOnce(Promise.resolve({
        errno: 0,
        msg: 'success',
        permission: -1
      }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <table>
        <tbody>
          <tr id="user-1">
            <td class="status"></td>
            <td id="ban-1" data="banned"></td>
          </tr>
        </tbody>
      </table>
    `;
    await require(modulePath).changeBanStatus(1);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=ban',
      dataType: 'json',
      data: { uid: 1 }
    });
    expect($('#ban-1').attr('data')).toBe('normal');
    expect($('#ban-1').text()).toBe('admin.ban');
    expect($('.status').text()).toBe('admin.normal');
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    document.body.innerHTML = `
      <table>
        <tbody>
          <tr id="user-1">
            <td class="status"></td>
            <td id="ban-1" data="normal"></td>
          </tr>
        </tbody>
      </table>
    `;
    await require(modulePath).changeBanStatus(1);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=ban',
      dataType: 'json',
      data: { uid: 1 }
    });
    expect($('#ban-1').attr('data')).toBe('banned');
    expect($('#ban-1').text()).toBe('admin.unban');
    expect($('.status').text()).toBe('admin.banned');

    await require(modulePath).changeBanStatus(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await require(modulePath).changeBanStatus(1);
    expect(showAjaxError).toBeCalled();
  });

  it('change admin status', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({
        errno: 0,
        msg: 'success',
        permission: 0
      }))
      .mockReturnValueOnce(Promise.resolve({
        errno: 0,
        msg: 'success',
        permission: 1
      }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <table>
        <tbody>
          <tr id="user-1">
            <td class="status"></td>
            <td id="admin-1" data="admin"></td>
          </tr>
        </tbody>
      </table>
    `;
    await require(modulePath).changeAdminStatus(1);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=admin',
      dataType: 'json',
      data: { uid: 1 }
    });
    expect($('#admin-1').attr('data')).toBe('normal');
    expect($('#admin-1').text()).toBe('admin.setAdmin');
    expect($('.status').text()).toBe('admin.normal');
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');

    document.body.innerHTML = `
      <table>
        <tbody>
          <tr id="user-1">
            <td class="status"></td>
            <td id="admin-1" data="normal"></td>
          </tr>
        </tbody>
      </table>
    `;
    await require(modulePath).changeAdminStatus(1);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=admin',
      dataType: 'json',
      data: { uid: 1 }
    });
    expect($('#admin-1').attr('data')).toBe('admin');
    expect($('#admin-1').text()).toBe('admin.unsetAdmin');
    expect($('.status').text()).toBe('admin.admin');

    await require(modulePath).changeAdminStatus(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await require(modulePath).changeAdminStatus(1);
    expect(showAjaxError).toBeCalled();
  });

  it('delete a user', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = '<tr id="user-1"></tr>';
    const deleteUserAccount = require(modulePath).deleteUserAccount;

    await deleteUserAccount(1);
    expect(fetch).not.toBeCalled();

    await deleteUserAccount(1);
    expect(swal).toBeCalledWith({
      text: 'admin.deleteUserNotice',
      type: 'warning',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/users?action=delete',
      dataType: 'json',
      data: { uid: 1 }
    });
    expect(document.getElementById('user-1')).toBeNull();

    await deleteUserAccount(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await deleteUserAccount(1);
    expect(showAjaxError).toBeCalled();
  });

  it('"input" element should be focused out when press enter key', () => {
    document.body.innerHTML = `
      <div id="user-1">
        <div>
          <input class="score" type="number" value="0" />
        </div>
      </div>
    `;

    require(modulePath);

    $('.score').focus();
    const event = $.Event('keypress');
    event.which = 13;
    $('.score').trigger(event);

    expect($('.score').is(':focus')).toBe(false);
  });
});

describe('tests for "common" module', () => {
  const modulePath = '../admin/common';

  it('send feedbacks', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'Recorded.' }));
    const docCookies = require('../common/cookie');

    window.document.cookie = '';
    window.docCookies = docCookies;
    window.fetch = fetch;
    window.console.log = jest.fn();
    window.blessing = {
        site_name: 'inm',
        base_url: 'http://tdkr.mur',
        version: '8.1.0'
    };

    const { sendFeedback } = require(modulePath);

    await sendFeedback();
    expect(fetch).toBeCalledWith({
        url: 'https://work.prinzeugen.net/statistics/feedback',
        type: 'POST',
        dataType: 'json',
        data: { site_name: 'inm', site_url: 'http://tdkr.mur', version: '8.1.0' }
    });
    expect(window.document.cookie).not.toBe('');
    expect(console.log).toBeCalledWith('Feedback sent. Thank you!');

    await sendFeedback();
    expect(fetch).toHaveBeenCalledTimes(1);
    expect(console.log).toHaveBeenCalledTimes(1);
  });

  it('initialize data tables', () => {
    $.fn.dataTable = { defaults: {} };
    const initUsersTable = jest.fn();
    const initPlayersTable = jest.fn();
    const initPluginsTable = jest.fn();
    window.initUsersTable = initUsersTable;
    window.initPlayersTable = initPlayersTable;
    window.initPluginsTable = initPluginsTable;
    const { initTables } = require(modulePath);

    document.body.innerHTML = '<div id="user-table"></div>';
    initTables();
    expect(initUsersTable).toBeCalled();

    document.body.innerHTML = '<div id="player-table"></div>';
    initTables();
    expect(initPlayersTable).toBeCalled();

    document.body.innerHTML = '<div id="plugin-table"></div>';
    initTables();
    expect($.pluginsTable).not.toBeNull();
  });
});
