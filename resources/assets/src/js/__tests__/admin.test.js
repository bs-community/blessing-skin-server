const $ = require('jquery');
window.$ = window.jQuery = $;

describe('tests for "customize" module', () => {
  const modulePath = '../admin/customize';

  it('change skin preview after switching color', () => {
    window.current_skin = 'skin-blue';
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
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.current_skin = '';

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
  });
});

describe('tests for "players" module', () => {
  const modulePath = '../admin/players';

  it('change player reference', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;

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
  });

  it('submit changed texture information', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const modal = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.$.fn.modal = modal;

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
  });

  it('change player name', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'success' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn(options => {
      options.inputValidator('newName');
      return Promise.resolve('newName');
    });
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;

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
    await changePlayerName(1, 'oldName');
    expect($('tr#1 > td:nth-child(3)').text()).toBe('newName');
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
  });

  it('change owner', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn().mockReturnValue(Promise.resolve(2));
    const debounce = jest.fn(fn => fn);
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;
    window.debounce = debounce;

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
    expect($('tr#1 > td:nth-child(2)').text()).toBe((2).toString());
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
  });

  it('delete player', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn().mockReturnValue(Promise.resolve('newName'));
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;

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
    await deletePlayer(1);
    expect(document.getElementById('1')).toBeNull();
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
  });
});

describe('tests for "plugins" module', () => {
  const modulePath = '../admin/plugins';

  it('enable a plugin', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const reloadTable = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    $.pluginsTable = {
      ajax: {
        reload: reloadTable
      }
    };

    const enablePlugin = require(modulePath).enablePlugin;

    await enablePlugin('plugin');
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/plugins?action=enable&name=plugin',
      dataType: 'json'
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect(reloadTable).toBeCalledWith(null, false);

    await enablePlugin('plugin');
    expect(toastr.warning).toBeCalledWith('warning');
  });

  it('disable a plugin', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const reloadTable = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    $.pluginsTable = {
      ajax: {
        reload: reloadTable
      }
    };

    const disablePlugin = require(modulePath).disablePlugin;

    await disablePlugin('plugin');
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/plugins?action=disable&name=plugin',
      dataType: 'json'
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect(reloadTable).toBeCalledWith(null, false);

    await disablePlugin('plugin');
    expect(toastr.warning).toBeCalledWith('warning');
  });

  it('delete a plugin', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'success' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const reloadTable = jest.fn();
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    $.pluginsTable = {
      ajax: {
        reload: reloadTable
      }
    };
    window.swal = swal;

    const deletePlugin = require(modulePath).deletePlugin;

    await deletePlugin('plugin');
    expect(swal).toBeCalledWith({
      text: 'admin.confirmDeletion',
      type: 'warning',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'admin/plugins?action=delete&name=plugin',
      dataType: 'json'
    });
    await deletePlugin('plugin');
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect(reloadTable).toBeCalledWith(null, false);
  });
});

describe('tests for "update" module', () => {
  it('download updates', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({
        file_size: 5000
      }))
      .mockReturnValueOnce(Promise.resolve());
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const modal = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    $.fn.modal = modal;

    document.body.innerHTML = `
      <div id="file-size"></div>
      <div id="modal-start-download"></div>
    `;

    await require('../admin/update')();
    expect(fetch).toBeCalledWith(expect.objectContaining({
      url: 'admin/update/download?action=prepare-download',
      type: 'GET',
      dataType: 'json',
    }));
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
  });
});

describe('tests for "users" module', () => {
  const modulePath = '../admin/users';

  it('change user email', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'success' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn(options => {
      options.inputValidator('a@b.c');
      return Promise.resolve('a@b.c');
    });
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;

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
    await changeUserEmail(1);
    expect($('tr > td:nth-child(2)').text()).toBe('a@b.c');
    expect(toastr.success).toBeCalledWith('success');
  });

  it('change user nick name', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'success' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn(options => {
      options.inputValidator('foo');
      return Promise.resolve('foo');
    });
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;

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
    await changeUserNickName(1);
    expect($('tr > td:nth-child(3)').text()).toBe('foo');
    expect(toastr.success).toBeCalledWith('success');
  });

  it('change user password', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'success' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn().mockReturnValue(Promise.resolve('secret'));
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;

    const changeUserPwd = require(modulePath).changeUserPwd;

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
    await changeUserPwd(1);
    expect(toastr.success).toBeCalledWith('success');
  });

  it('change user score', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;

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
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;

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
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;

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
  });

  it('delete a user', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'success' }));
    const url = jest.fn(path => path);
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    const trans = jest.fn(key => key);
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    window.fetch = fetch;
    window.url = url;
    window.toastr = toastr;
    window.trans = trans;
    window.swal = swal;

    document.body.innerHTML = '<tr id="user-1"></tr>';
    const deleteUserAccount = require(modulePath).deleteUserAccount;

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
    await deleteUserAccount(1);
    expect(document.getElementById('user-1')).toBeNull();
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
