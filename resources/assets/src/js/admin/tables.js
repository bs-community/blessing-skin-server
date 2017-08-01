'use strict';

function initUsersTable() {
  let uid = getQueryString('uid');
  let dataUrl = url('admin/user-data') + (uid ? `?uid=${uid}` : '');

  $('#user-table').DataTable({
    ajax: dataUrl,
    scrollY: ($('.content-wrapper').height() - $('.content-header').outerHeight()) * 0.7,
    rowCallback: (row, data) => {
      $(row).attr('id', `user-${data.uid}`);
    },
    columnDefs: [
      {
        targets: 0,
        data: 'uid',
        width: '1%'
      },
      {
        targets: 1,
        data: 'email'
      },
      {
        targets: 2,
        data: 'nickname'
      },
      {
        targets: 3,
        data: 'score',
        render: data => {
          return `<input type="number" class="form-control score" value="${data}" title="${trans('admin.scoreTip')}" data-toggle="tooltip" data-placement="right">`;
        }
      },
      {
        targets: 4,
        data: 'players_count',
        searchable: false,
        orderable: false,
        render: (data, type, row) => {
          return `<span title="${trans('admin.doubleClickToSeePlayers')}"
          style="cursor: pointer;"
          ondblclick="window.location.href = '${url('admin/players?uid=') + row.uid}'"
          data-toggle="tooltip" data-placement="top">${data}</span>`;
        }
      },
      {
        targets: 5,
        data: 'permission',
        className: 'status',
        render: data => {
          switch (data) {
            case -1:
              return trans('admin.banned');
            case 0:
              return trans('admin.normal');
            case 1:
              return trans('admin.admin');
            case 2:
              return trans('admin.superAdmin');
          }
        }
      },
      {
        targets: 6,
        data: 'register_at'
      },
      {
        targets: 7,
        data: 'operations',
        searchable: false,
        orderable: false,
        render: (data, type, row) => {
          let adminOption = '', bannedOption = '', deleteUserButton;
          if (row.permission !== 2) {
            if (data === 2) {
              if (row.permission === 1) {
                adminOption = `<li class="divider"></li>
                <li><a id="admin-${row.uid}" data="admin" href="javascript:changeAdminStatus(${row.uid});">${trans('admin.unsetAdmin')}</a></li>`;
              } else {
                adminOption = `<li class="divider"></li>
                <li><a id="admin-${row.uid}" data="normal" href="javascript:changeAdminStatus(${row.uid});">${trans('admin.setAdmin')}</a></li>`;
              }
            }
            if (row.permission === -1) {
              bannedOption = `<li class="divider"></li>
              <li><a id="ban-${row.uid}" data="banned" href="javascript:changeBanStatus(${row.uid});">${trans('admin.unban')}</a></li>`;
            } else {
              bannedOption = `<li class="divider"></li>
              <li><a id="ban-${row.uid}" data="normal" href="javascript:changeBanStatus(${row.uid});">${trans('admin.ban')}</a></li>`;
            }
          }

          if (data === 2) {
            if (row.permission === 2) {
              deleteUserButton = `
              <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="${trans('admin.cannotDeleteSuperAdmin')}">${trans('admin.deleteUser')}</a>`;
            } else {
              deleteUserButton = `
              <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount(${row.uid});">${trans('admin.deleteUser')}</a>`;
            }
          } else {
            if (row.permission === 1 || row.permission === 2) {
              deleteUserButton = `
              <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="${trans('admin.cannotDeleteAdmin')}">${trans('admin.deleteUser')}</a>`;
            } else {
              deleteUserButton = `
              <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount(${row.uid});">${trans('admin.deleteUser')}</a>`;
            }
          }

          return `
          <div class="btn-group">
          <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          ${trans('admin.operationsTitle')} <span class="caret"></span></button>
          <ul class="dropdown-menu">
            <li><a href="javascript:changeUserEmail(${row.uid});">${trans('admin.changeEmail')}</a></li>
            <li><a href="javascript:changeUserNickName(${row.uid});">${trans('admin.changeNickName')}</a></li>
            <li><a href="javascript:changeUserPwd(${row.uid});">${trans('admin.changePassword')}</a></li>
            ${adminOption}${bannedOption}
          </ul>
          </div>
          ${deleteUserButton}`;
        }
      }
    ]
  });
}

function initPlayersTable() {
  let uid = getQueryString('uid');
  let dataUrl = url('admin/player-data') + (uid ? `?uid=${uid}` : '');

  $('#player-table').DataTable({
    ajax: dataUrl,
    scrollY: ($('.content-wrapper').height() - $('.content-header').outerHeight()) * 0.7,
    columnDefs: [
      {
        targets: 0,
        data: 'pid',
        width: '1%'
      },
      {
        targets: 1,
        data: 'uid',
        render: (data, type, row) => {
          return `<span title="${trans('admin.doubleClickToSeeUser')}"
          style="cursor: pointer;"
          ondblclick="window.location.href = '${url('admin/users?uid=') + row.uid}'"
          data-toggle="tooltip" data-placement="top">${data}</span>`;
        }
      },
      {
        targets: 2,
        data: 'player_name'
      },
      {
        targets: 3,
        data: 'preference',
        render: data => {
          return `
          <select class="form-control" onchange="changePreference.call(this)">
            <option ${(data == 'default') ? 'selected=selected' : ''} value="default">Default</option>
            <option ${(data == 'slim') ? 'selected=selected' : ''} value="slim">Slim</option>
          </select>`;
        }
      },
      {
        targets: 4,
        searchable: false,
        orderable: false,
        render: (data, type, row) => {
          let html = { steve: '', alex: '', cape: '' };
          ['steve', 'alex', 'cape'].forEach(textureType => {
            if (row['tid_' + textureType] === 0) {
              html[textureType] = `<img id="${row.pid}-${row['tid_' + textureType]}" width="64" />`;
            } else {
              html[textureType] = `
            <a href="${url('/')}skinlib/show/${row['tid_' + textureType]}">
              <img id="${row.pid}-${row['tid_' + textureType]}" width="64" src="${url('/')}preview/64/${row['tid_' + textureType]}.png" />
            </a>`;
            }
          });
          return html.steve + html.alex + html.cape;
        }
      },
      {
        targets: 5,
        data: 'last_modified'
      },
      {
        targets: 6,
        searchable: false,
        orderable: false,
        render: (data, type, row) => {
          return `
          <div class="btn-group">
          <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          ${trans('admin.operationsTitle')} <span class="caret"></span></button>
          <ul class="dropdown-menu">
            <li><a href="javascript:changeTexture(${row.pid}, '${row.player_name}');">${trans('admin.changeTexture')}</a></li>
            <li><a href="javascript:changePlayerName(${row.pid}, '${row.player_name}');">${trans('admin.changePlayerName')}</a></li>
            <li><a href="javascript:changeOwner(${row.pid});">${trans('admin.changeOwner')}</a></li>
          </ul>
          </div>
          <a class="btn btn-danger btn-sm" href="javascript:deletePlayer(${row.pid});">${trans('admin.deletePlayer')}</a>`;
        }
      }
    ]
  });
}

function initPluginsTable() {
  return $('#plugin-table').DataTable({
    ajax: url('admin/plugins/data'),
    columnDefs: [
      {
        targets: 0,
        data: 'title'
      },
      {
        targets: 1,
        data: 'description',
        width: '35%'
      },
      {
        targets: 2,
        data: 'author',
        render: data => {
          if (data.url === '' || data.url === null) {
            return data.author;
          } else {
            return `<a href="${data.url}" target="_blank">${data.author}</a>`;
          }
        }
      },
      {
        targets: 3,
        data: 'version'
      },
      {
        targets: 4,
        data: 'status'
      },
      {
        targets: 5,
        data: 'operations',
        searchable: false,
        orderable: false,
        render: (data, type, row) => {
          let switchEnableButton, configViewButton, deletePluginButton;
          if (data.enabled) {
            switchEnableButton = `
            <a class="btn btn-warning btn-sm" href="javascript:disablePlugin('${row.name}');">${trans('admin.disablePlugin')}</a>`;
          } else {
            switchEnableButton = `
            <a class="btn btn-primary btn-sm" href="javascript:enablePlugin('${row.name}');">${trans('admin.enablePlugin')}</a>`;
          }
          if (data.enabled && data.hasConfigView) {
            configViewButton = `
            <a class="btn btn-default btn-sm" href="${url('/')}admin/plugins/config/${row.name}">${trans('admin.configurePlugin')}</a>`;
          } else {
            configViewButton = `
            <a class="btn btn-default btn-sm" disabled="disabled" title="${trans('admin.noPluginConfigNotice')}" data-toggle="tooltip" data-placement="top">${trans('admin.configurePlugin')}</a>`;
          }
          deletePluginButton = `
          <a class="btn btn-danger btn-sm" href="javascript:deletePlugin('${row.name}');">${trans('admin.deletePlugin')}</a>`;
          return switchEnableButton + configViewButton + deletePluginButton;
        }
      }
    ]
  });
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        initUsersTable,
        initPlayersTable,
        initPluginsTable,
    };
}
