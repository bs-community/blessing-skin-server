/* eslint no-unused-vars: "off" */

const $ = require('jquery');
window.$ = window.jQuery = $;

window.getQueryString = jest.fn((key, defaultValue) => defaultValue);

describe('tests for "index" module', () => {
  const modulePath = '../skinlib/index';

  it('render skin library', () => {
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const jqPaginator = jest.fn();
    window.trans = trans;
    window.url = url;
    $.fn.jqPaginator = jqPaginator;

    document.body.innerHTML = `
      <div class="overlay"></div>
      <div id="skinlib-container"></div>
      <div id="skinlib-paginator"></div>
    `;
    const renderSkinlib = require(modulePath).renderSkinlib;

    renderSkinlib([]);
    expect($('#skinlib-container').html()).toBe(
      '<p style="text-align: center; margin: 30px 0;">general.noResult</p>'
    );
    expect($('#skinlib-paginator').css('display')).toBe('none');
    expect($('.overlay').css('display')).toBe('none');

    renderSkinlib([{
      tid: 1,
      name: 'name',
      type: 'steve',
      public: 0
    }]);
    expect($('#skinlib-paginator').css('display')).not.toBe('none');
    expect($('.item').attr('tid')).toBe('1');
    expect($('.item-body > img').attr('src')).toBe('preview/1.png');
    expect($('.texture-name > span').attr('title')).toBe('name');
    expect($('.texture-name > span > small').text()).toBe('skinlib.filter.steve');
    expect($('a.more.like').attr('title')).toBe('skinlib.anonymous');
    expect($('a.more.like').hasClass('liked')).toBe(false);
    expect($('a.more.like').hasClass('anonymous')).toBe(true);
    expect($('small.more').hasClass('hide')).toBe(false);
    expect($('small.private-label').text().trim()).toBe('skinlib.private');

    renderSkinlib([{
      tid: 1,
      name: 'name',
      type: 'steve',
      public: 0,
      liked: true
    }]);
    expect($('a.more.like').attr('title')).toBe('skinlib.removeFromCloset');
    expect($('a.more.like').hasClass('liked')).toBe(true);
    expect($('a.more.like').hasClass('anonymous')).toBe(false);
    expect($('small.more').hasClass('hide')).toBe(false);
    expect($('small.private-label').text().trim()).toBe('skinlib.private');

    renderSkinlib([{
      tid: 1,
      name: 'name',
      type: 'steve',
      public: 0,
      liked: false
    }]);
    expect($('a.more.like').attr('title')).toBe('skinlib.addToCloset');
    expect($('a.more.like').hasClass('liked')).toBe(false);
    expect($('a.more.like').hasClass('anonymous')).toBe(false);
    expect($('small.more').hasClass('hide')).toBe(false);
    expect($('small.private-label').text().trim()).toBe('skinlib.private');

    renderSkinlib([{
      tid: 1,
      name: 'name',
      type: 'steve',
      public: 1,
      liked: false
    }]);
    expect($('small.more').hasClass('hide')).toBe(true);
  });

  it('update paginator', () => {
    const trans = jest.fn(key => key);
    const jqPaginator = jest.fn();
    window.trans = trans;
    $.fn.jqPaginator = jqPaginator;

    document.body.innerHTML = `
      <p class="pagination"></p>
      <div id="skinlib-paginator"></div>
      <select class="pagination"></select>
    `;
    const updatePaginator = require(modulePath).updatePaginator;

    updatePaginator(2, 2);
    expect(trans).toBeCalledWith('general.pagination', { page: 2, total: 2 });
    expect(jqPaginator).toBeCalledWith(expect.objectContaining({
      currentPage: 2,
      totalPages: 2
    }));
    expect($('option').length).toBe(2);
    expect($('option[value=1]').prop('selected')).toBe(false);
    expect($('option[value=2]').prop('selected')).toBe(true);

    $('#skinlib-paginator').html('something');
    updatePaginator(2, 2);
    expect(jqPaginator).toBeCalledWith('option', {
      currentPage: 2,
      totalPages: 2
    });
  });

  it('update breadcrumb', () => {
    const trans = jest.fn(key => key);
    const jqPaginator = jest.fn();
    window.trans = trans;
    $.fn.jqPaginator = jqPaginator;

    document.body.innerHTML = `
      <div id="filter-indicator"></div>
      <div id="uploader-indicator"></div>
      <div id="sort-indicator"></div>
      <div id="search-indicator"></div>
      <input id="navbar-search-input" />
    `;
    const updateBreadCrumb = require(modulePath).updateBreadCrumb;

    updateBreadCrumb();
    expect($('#filter-indicator').html().replace(/\s/g, '')).toBe(
      'general.skin<small>skinlib.filter.skin</small>'
    );

    $.skinlib.filter = 'cape';
    updateBreadCrumb();
    expect($('#filter-indicator').html()).toBe('general.cape');

    expect($('#uploader-indicator').html()).toBe('skinlib.filter.allUsers');
    $.skinlib.uploader = 1;
    updateBreadCrumb();
    expect(trans).toBeCalledWith('skinlib.filter.uploader', { uid: 1 });
    expect($('#uploader-indicator').html()).toBe('skinlib.filter.uploader');

    expect($('#sort-indicator').html()).toBe('skinlib.sort.time');

    $.skinlib.keyword = '%20q';
    updateBreadCrumb();
    expect(trans).lastCalledWith(
      'general.searchResult',
      { keyword: ' q' }
    );
    expect($('#search-indicator').html()).toBe('general.searchResult');
    expect($('#navbar-search-input').val()).toBe(' q');
  });

  it('reload skin library', async () => {
    const fetch = jest.fn().mockReturnValueOnce(Promise.resolve({
      items: []
    })).mockReturnValueOnce(Promise.reject());
    const url = jest.fn(path => path);
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.url = url;
    window.showAjaxError = showAjaxError;
    document.body.innerHTML = `
      <div id="skinlib-paginator"></div>
    `;
    const reloadSkinlib = require(modulePath).reloadSkinlib;
    window.history.pushState = jest.fn();

    await reloadSkinlib();
    expect(fetch).toBeCalledWith(expect.objectContaining({
      type: 'GET',
      url: 'skinlib/data',
      dataType: 'json',
      data: {
        page: 2,
        filter: 'cape',
        sort: 'time',
        uploader: 1,
        keyword: '%20q'
      }
    }));
  });

  it('update query string', () => {
    const url = jest.fn(path => path);
    window.url = url;
    document.body.innerHTML = `
      <ul>
        <li class="locale" data-code="zh_CN"><a></a></li>
        <li class="locale" data-code="en"><a></a></li>
      </ul>
    `;
    const updateUrlQueryString = require(modulePath).updateUrlQueryString;
    window.history.pushState = jest.fn();

    const query = 'page=2&filter=cape&sort=time&uploader=1&keyword=%2520q';

    updateUrlQueryString();
    expect(window.history.pushState).toBeCalledWith(
      null,
      null,
      'skinlib?' + query);
    expect($('li[data-code=zh_CN] > a').prop('href')).toBe(`?lang=zh_CN&${query}`);
    expect($('li[data-code=en] > a').prop('href')).toBe(`?lang=en&${query}`);
  });
});

describe('tests for "operations" module', () => {
  const modulePath = '../skinlib/operations';

  it('add to closet', async () => {
    const url = jest.fn(path => path);
    window.url = url;
    const trans = jest.fn(key => key);
    window.trans = trans;
    const swal = jest.fn(option => {
      option.inputValidator('custom');
      return Promise.resolve('custom');
    });
    window.swal = swal;
    $.getJSON = jest.fn((option, cb) => {
      cb({ name: 'name' });
    });

    const addToCloset = require(modulePath).addToCloset;

    await addToCloset(1);
    expect($.getJSON.mock.calls[0][0]).toBe('skinlib/info/1');
    expect(swal).toBeCalledWith(expect.objectContaining({
      title: 'skinlib.setItemName',
      inputValue: 'name',
      input: 'text',
      showCancelButton: true,
    }));
  });

  it('add to closet (by ajax)', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    window.fetch = fetch;
    const url = jest.fn(path => path);
    window.url = url;
    const trans = jest.fn(key => key);
    window.trans = trans;
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    window.swal = swal;
    const modal = jest.fn();
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.toastr = toastr;
    const showAjaxError = jest.fn();
    window.showAjaxError = showAjaxError;
    $.fn.modal = modal;

    document.body.innerHTML = `
      <div class="modal" style="display: none" id="shouldBeRemoved"></div>
      <div class="modal" id="shouldNotBeRemoved"></div>
    `;
    const ajaxAddToCloset = require(modulePath).ajaxAddToCloset;

    await ajaxAddToCloset(1, 'name');
    expect(document.getElementById('shouldBeRemoved')).toBeNull();
    expect(document.getElementById('shouldNotBeRemoved')).not.toBeNull();
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'user/closet/add',
      dataType: 'json',
      data: { tid: 1, name: 'name' }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect(modal).toBeCalledWith('hide');

    await ajaxAddToCloset(1, 'name');
    expect(toastr.warning).toBeCalledWith('warning');

    await ajaxAddToCloset(1, 'name');
    expect(showAjaxError).toBeCalled();
  });

  it('remove from closet', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    window.fetch = fetch;
    const url = jest.fn(path => path);
    window.url = url;
    const trans = jest.fn(key => key);
    window.trans = trans;
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    window.swal = swal;
    const modal = jest.fn();
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.toastr = toastr;
    const showAjaxError = jest.fn();
    window.showAjaxError = showAjaxError;

    const removeFromCloset = require(modulePath).removeFromCloset;

    await removeFromCloset(1);
    expect(fetch).not.toBeCalled();

    await removeFromCloset(1);
    expect(swal).toBeCalledWith({
      text: 'user.removeFromClosetNotice',
      type: 'warning',
      showCancelButton: true,
      cancelButtonColor: '#3085d6',
      confirmButtonColor: '#d33'
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: '/user/closet/remove',
      dataType: 'json',
      data: { tid: 1 }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });

    await removeFromCloset(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await removeFromCloset(1);
    expect(showAjaxError).toBeCalled();
  });

  it('change texture name', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    window.fetch = fetch;
    const url = jest.fn(path => path);
    window.url = url;
    const trans = jest.fn(key => key);
    window.trans = trans;
    const swal = jest.fn()
      .mockImplementationOnce(() => Promise.reject())
      .mockImplementationOnce(option => {
        option.inputValidator('new-name');
        return Promise.resolve('new-name');
      });
    window.swal = swal;
    const modal = jest.fn();
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.toastr = toastr;
    const showAjaxError = jest.fn();
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = '<div id="name"></div>';
    const changeTextureName = require(modulePath).changeTextureName;

    await changeTextureName(1, 'oldName');
    expect(fetch).not.toBeCalled();

    await changeTextureName(1, 'oldName');
    expect(swal).toBeCalledWith(expect.objectContaining({
      text: 'skinlib.setNewTextureName',
      input: 'text',
      inputValue: 'oldName',
      showCancelButton: true,
    }));
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'skinlib/rename',
      dataType: 'json',
      data: { tid: 1, new_name: 'new-name' }
    });
    expect($('div').text()).toBe('new-name');
    expect(toastr.success).toBeCalledWith('success');

    await changeTextureName(1, 'oldName');
    expect(toastr.warning).toBeCalledWith('warning');

    await changeTextureName(1, 'oldName');
    expect(showAjaxError).toBeCalled();
  });

  it('update texture status', () => {
    window.trans = jest.fn(key => key);
    document.body.innerHTML = `
      <div id="likes">5</div>
      <a tid="1"></a>
      <a id="1"></a>
    `;
    const updateTextureStatus = require(modulePath).updateTextureStatus;

    updateTextureStatus(1, 'add');
    expect($('a[tid=1]').attr('href')).toBe('javascript:removeFromCloset(1);');
    expect($('a[tid=1]').attr('title')).toBe('skinlib.removeFromCloset');
    expect($('a[tid=1]').hasClass('liked')).toBe(true);
    expect($('#1').attr('href')).toBe('javascript:removeFromCloset(1);');
    expect($('#1').html()).toBe('skinlib.removeFromCloset');
    expect($('div').html()).toBe('6');

    updateTextureStatus(1, 'remove');
    expect($('a[tid=1]').attr('href')).toBe('javascript:addToCloset(1);');
    expect($('a[tid=1]').attr('title')).toBe('skinlib.addToCloset');
    expect($('a[tid=1]').hasClass('liked')).toBe(false);
    expect($('#1').attr('href')).toBe('javascript:addToCloset(1);');
    expect($('#1').html()).toBe('skinlib.addToCloset');
    expect($('div').html()).toBe('5');
  });

  it('click changing privacy button', async () => {
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ errno: 0, msg: 'success' }));
    window.fetch = fetch;
    const url = jest.fn(path => path);
    window.url = url;
    const trans = jest.fn(key => key);
    window.trans = trans;
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    window.swal = swal;
    const modal = jest.fn();
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.toastr = toastr;

    document.body.innerHTML = '<a class="private-label"></a>';
    require(modulePath);

    await $('a').click();
    expect(swal).toBeCalledWith({
      text: 'skinlib.setPublicNotice',
      type: 'warning',
      showCancelButton: true
    });
    expect(document.getElementsByTagName('a').length).toBe(0);
  });

  it('change privacy', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success', public: '0' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    window.fetch = fetch;
    const url = jest.fn(path => path);
    window.url = url;
    const trans = jest.fn(key => key);
    window.trans = trans;
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    window.swal = swal;
    const modal = jest.fn();
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.toastr = toastr;
    const showAjaxError = jest.fn();
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <a id="1">skinlib.setAsPrivate</a>
      <a id="2">skinlib.setAsPublic</a>
    `;
    const changePrivacy = require(modulePath).changePrivacy;

    await changePrivacy(1);
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'skinlib/privacy',
      dataType: 'json',
      data: { tid: 1 }
    });
    expect(toastr.warning).not.toBeCalled();
    expect(toastr.success).toBeCalledWith('success');
    expect($('#1').html()).toBe('skinlib.setAsPublic');

    await changePrivacy(1);
    expect($('#2').html()).toBe('skinlib.setAsPrivate');

    await changePrivacy(1);
    expect(toastr.warning).toBeCalledWith('warning');

    await changePrivacy(1);
    expect(showAjaxError).toBeCalled();
  });

  it('delete texture', async () => {
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ errno: 0, msg: 'success' }))
      .mockReturnValueOnce(Promise.resolve({ errno: 1, msg: 'warning' }))
      .mockReturnValueOnce(Promise.reject());
    window.fetch = fetch;
    const url = jest.fn(path => path);
    window.url = url;
    const trans = jest.fn(key => key);
    window.trans = trans;
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    window.swal = swal;
    const modal = jest.fn();
    const toastr = {
      success: jest.fn(),
      warning: jest.fn()
    };
    window.toastr = toastr;
    const showAjaxError = jest.fn();
    window.showAjaxError = showAjaxError;

    const deleteTexture = require(modulePath).deleteTexture;

    await deleteTexture(1);
    expect(fetch).not.toBeCalled();

    await deleteTexture(1);
    expect(swal).toBeCalledWith({
      text: 'skinlib.deleteNotice',
      type: 'warning',
      showCancelButton: true
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'skinlib/delete',
      dataType: 'json',
      data: { tid: 1 }
    });
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
    expect(url).toBeCalledWith('skinlib');

    await deleteTexture(1);
    expect(swal).toBeCalledWith({ type: 'warning', html: 'warning' });

    await deleteTexture(1);
    expect(showAjaxError).toBeCalled();
  });
});
