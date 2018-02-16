/* eslint no-unused-vars: "off" */

const $ = require('jquery');
window.jQuery = window.$ = $;

describe('tests for "i18n" module', () => {
  const modulePath = '../common/i18n';

  it('load locales', () => {
    window.isEmpty = obj => !obj;  // Just for test
    const loadLocales = require(modulePath).loadLocales;

    $.locales = {
      en: { text: 'text', nested: { sth: ':sth here!' } }
    };

    loadLocales();
    expect($.currentLocale).toEqual({ text: 'text', nested: { sth: ':sth here!' } });
  });

  it('get translated text', () => {
    const trans = require(modulePath).trans;

    $.currentLocale = undefined;  // Should load locale automatically
    $.locales = {
      en: { text: 'text', nested: { sth: ':sth here!' } }
    };

    expect(trans('text')).toBe('text');
    expect(trans('text.nothing')).toBe('text.nothing');
    expect(trans('nested.sth')).toBe(':sth here!');
    expect(trans('nested.sth', { sth: 'abc' })).toBe('abc here!');
  });
});

jest.useFakeTimers();

describe('tests for "logout" module', () => {
  const modulePath = '../common/logout';

  it('logout', async () => {
    const swal = jest.fn()
      .mockReturnValueOnce(Promise.reject())
      .mockReturnValueOnce(Promise.resolve());
    const trans = jest.fn(key => key);
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ msg: 'success' }))
      .mockReturnValueOnce(Promise.reject());
    const showAjaxError = jest.fn();
    window.swal = swal;
    window.trans = trans;
    window.fetch = fetch;
    window.url = jest.fn(path => path);
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = '<button id="logout-button"></button>';
    require(modulePath);

    await $('button').click();
    expect(fetch).not.toBeCalled();

    await $('button').click();
    expect(swal).toBeCalledWith({
      text: 'general.confirmLogout',
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: 'general.confirm',
      cancelButtonText: 'general.cancel'
    });
    expect(fetch).toBeCalledWith({
      type: 'POST',
      url: 'auth/logout',
      dataType: 'json'
    });
    jest.runAllTimers();
    expect(url).toBeCalled();

    await $('button').click();
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });

    await $('button').click();
    expect(showAjaxError).toBeCalled();
  });
});

jest.useRealTimers();

describe('tests for "notify" module', () => {
  const modulePath = '../common/notify';

  it('show message', () => {
    document.body.innerHTML = '<div id="msg" class="a-class"></div>';

    const showMsg = require(modulePath).showMsg;

    showMsg('msg1');
    expect($('div').hasClass('a-class')).toBe(false);
    expect($('div').hasClass('callout')).toBe(true);
    expect($('div').hasClass('callout-info')).toBe(true);
    expect($('div').html()).toBe('msg1');

    showMsg('msg2', 'warning');
    expect($('div').hasClass('callout-info')).toBe(false);
    expect($('div').hasClass('callout')).toBe(true);
    expect($('div').hasClass('callout-warning')).toBe(true);
    expect($('div').html()).toBe('msg2');
  });

  it('show ajax error', () => {
    window.trans = jest.fn(key => key);
    $.fn.modal = jest.fn();

    const showAjaxError = require(modulePath).showAjaxError;

    showAjaxError({ responseText: 'error' });
    expect(window.trans).toBeCalledWith('general.fatalError');
  });

  it('show modal dialog', () => {
    const modal = jest.fn();
    $.fn.modal = modal;

    const showModal = require(modulePath).showModal;
    showModal('');
    expect(modal).toBeCalled();
  });
});

describe('tests for "polyfill" module', () => {
  const modulePath = '../common/polyfill';

  String.prototype.includes = undefined;
  String.prototype.endsWith = undefined;
  require(modulePath);

  it('String#includes', () => {
    expect('blessing-skin'.includes('skin')).toBe(true);
    expect('blessing-skin'.includes('server')).toBe(false);
    expect('blessing-skin'.includes('skin', 9)).toBe(true);
    expect('blessing-skin'.includes('blessing', 9)).toBe(false);
  });

  it('String#endsWith', () => {
    expect('blessing-skin'.endsWith('skin')).toBe(true);
    expect('blessing-skin'.endsWith('server')).toBe(false);
    expect('blessing-skin'.endsWith('blessing', 8)).toBe(true);
  });
});

describe('tests for "texture-preview" module', () => {
  const TexturePreview = require('../common/texture-preview');

  it('change 2d preview', () => {
    const url = jest.fn(path => path);
    window.url = url;

    document.body.innerHTML = `
      <a><img id="steve" /></a>
      <div></div>
    `;

    const instance = new TexturePreview('steve', 5, 'default');
    instance.change2dPreview();
    expect($('img').attr('src')).toBe('preview/200/5.png');
    expect($('a').attr('href')).toBe('skinlib/show/5');
    expect($('div').css('display')).toBe('none');
  });

  it('change 3d preview', async () => {
    const url = jest.fn(path => path);
    const fetch = jest.fn()
      .mockReturnValueOnce(Promise.resolve({ hash: '1' }))
      .mockReturnValueOnce(Promise.resolve({ hash: '2' }))
      .mockReturnValueOnce(Promise.reject());
    const showAjaxError = jest.fn();
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    window.url = url;
    window.fetch = fetch;
    window.showAjaxError = showAjaxError;
    window.MSP = MSP;

    let instance = new TexturePreview('alex', 5, 'default');
    instance.change3dPreview();
    expect(fetch).not.toBeCalled();

    instance = new TexturePreview('steve', 5, 'default');
    await instance.change3dPreview();
    expect(fetch).toBeCalledWith({
      type: 'GET',
      url: 'skinlib/info/5',
      dataType: 'json'
    });
    expect(MSP.changeSkin).toBeCalledWith('textures/1');

    instance = new TexturePreview('cape', 5, 'default');
    await instance.change3dPreview();
    expect(fetch).toBeCalledWith({
      type: 'GET',
      url: 'skinlib/info/5',
      dataType: 'json'
    });
    expect(MSP.changeCape).toBeCalledWith('textures/2');

    await instance.change3dPreview();
    expect(showAjaxError).toBeCalled();
  });

  it('show not uploaded', () => {
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    window.MSP = MSP;
    document.body.innerHTML = `
      <a><img id="steve" /></a>
      <div></div>
    `;

    let instance = new TexturePreview('steve', 5, 'default');
    instance.showNotUploaded();
    expect($('img').css('display')).toBe('none');
    expect($('div').css('display')).not.toBe('none');

    instance = new TexturePreview('cape', 7, 'cape');
    instance.showNotUploaded();
    expect(MSP.changeCape).toBeCalledWith('');
  });

  it('show 3d preview', () => {
    document.body.innerHTML = `
      <a id="preview-2d"></a>
      <a class="operations" style="display: none"></a>
      <a id="preview-switch"></a>
      <div id="skinpreview"></div>
    `;
    const trans = jest.fn(key => key);
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn(),
      get3dSkinCanvas: jest.fn(() => '<canvas></canvas>')
    };
    window.trans = trans;
    window.MSP = MSP;

    TexturePreview.previewType = '3D';
    TexturePreview.show3dPreview();
    expect($('#preview-2d').css('display')).toBe('none');
    expect($('.operations').css('display')).not.toBe('none');
    expect($('#preview-switch').html()).toBe('user.switch2dPreview');
    expect(MSP.get3dSkinCanvas).toBeCalledWith(0, 0);
  });

  it('show 2d preview', () => {
    document.body.innerHTML = `
      <div id="canvas3d"></div>
      <a id="preview-2d" style="display: none"></a>
      <a class="operations"></a>
      <a id="preview-switch"></a>
    `;
    const trans = jest.fn(key => key);
    const MSP = {
      changeSkin: jest.fn(),
      changeCape: jest.fn()
    };
    window.trans = trans;
    window.MSP = MSP;

    TexturePreview.show2dPreview();
    expect(document.getElementById('canvas3d')).toBeNull();
    expect($('#preview-2d').css('display')).not.toBe('none');
    expect($('.operations').css('display')).toBe('none');
    expect($('#preview-switch').html()).toBe('user.switch3dPreview');
  });
});

describe('tests for "utils" module', () => {
  const modulePath = '../common/utils';

  window.blessing = {
    version: ''
  };

  it('check a variable if it is empty', () => {
    const isEmpty = require(modulePath).isEmpty;

    expect(isEmpty()).toBe(true);
    expect(isEmpty(null)).toBe(true);
    expect(isEmpty(undefined)).toBe(true);
    expect(isEmpty(0)).toBe(false);
    expect(isEmpty(false)).toBe(false);
    expect(isEmpty('')).toBe(true);
    expect(isEmpty({})).toBe(true);
    expect(isEmpty({ sth: '' })).toBe(false);
    expect(isEmpty([])).toBe(true);
    expect(isEmpty([1])).toBe(false);
    expect(isEmpty('something')).toBe(false);
    expect(isEmpty(Symbol())).toBe(true);
  });

  it('fake fetch', () => {
    $.ajax = jest.fn();
    const fetch = require(modulePath).fetch;
    const xhr = fetch({ type: 'GET' });

    expect($.ajax).toBeCalledWith({ type: 'GET' });
    expect(Object.getPrototypeOf(xhr)).toBe(Promise.prototype);
  });

  it('make a debounced function', done => {
    const func = jest.fn();
    const debounce = require(modulePath).debounce;
    const debounced = debounce(func, 100);

    debounced();
    debounced();

    setTimeout(() => {
      expect(func.mock.calls.length).toBe(1);
      done();
    }, 100);
    jest.runAllTimers();

    expect(() => debounce(func, 'string')).toThrow();
    expect(() => debounce('not a function', 100)).toThrow();
  });

  it('get a absolute url', () => {
    window.blessing = { base_url: 'http://localhost' };
    const url = require(modulePath).url;

    expect(url()).toBe('http://localhost/');
    expect(url('test')).toBe('http://localhost/test');
    expect(url('/test')).toBe('http://localhost/test');
  });

  it('get argument from query string', () => {
    const { getQueryString } = require(modulePath);

    Object.defineProperty(window.location, 'search', {
      value: '?key1=val1',
      writable: true
    });
    expect(getQueryString('key1')).toBe('val1');
    expect(getQueryString('key2')).toBeUndefined();
    expect(getQueryString('key2', 'val2')).toBe('val2');
    window.location.search = '?key1=val1&key2=val2';
    expect(getQueryString('key2', 'val2')).toBe('val2');
  });

  it('test `isMobileBrowserScrolling` function', () => {
    const { isMobileBrowserScrolling } = require(modulePath);
    expect(isMobileBrowserScrolling()).toBe(false);

    $.cachedWindowWidth = 50;
    $.cachedWindowHeight = 50;
    expect(isMobileBrowserScrolling()).toBe(false);

    $.cachedWindowWidth = 60;
    $.cachedWindowHeight = 0;
    expect(isMobileBrowserScrolling()).toBe(false);

    $.cachedWindowWidth = 0;
    $.cachedWindowHeight = 50;
    expect(isMobileBrowserScrolling()).toBe(true);
    expect(isMobileBrowserScrolling()).toBe(true);  // For the second condition

    $.lastWindowHeight = 60;
    expect(isMobileBrowserScrolling()).toBe(false);
  });
});
