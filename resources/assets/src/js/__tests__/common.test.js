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
    
    expect(trans('text')).toBe('text');
    expect(trans('text.nothing')).toBe('text.nothing');
    expect(trans('nested.sth')).toBe(':sth here!');
    expect(trans('nested.sth', { sth: 'abc' })).toBe('abc here!');
  });
});

describe('tests for "logout" module', () => {
  const modulePath = '../common/logout';

  it('logout', async () => {
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    const trans = jest.fn(key => key);
    const fetch = jest.fn()
      .mockReturnValue(Promise.resolve({ msg: 'success' }));
    window.swal = swal;
    window.trans = trans;
    window.fetch = fetch;
    window.url = jest.fn(path => path);

    document.body.innerHTML = '<button id="logout-button"></button>';
    require(modulePath);

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
    await $('button').click();
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });
  });
});

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
    const warn = jest.fn();
    window.console.warn = warn;
    window.trans = jest.fn(key => key);

    const showAjaxError = require(modulePath).showAjaxError;

    showAjaxError('error');
    expect(warn).toBeCalledWith('error');

    showAjaxError({});
    expect(warn).toBeCalledWith('Empty Ajax response body.');
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
    const debounced = require(modulePath).debounce(func, 100);

    debounced();
    debounced();

    setTimeout(() => {
      expect(func.mock.calls.length).toBe(1);
      done();
    }, 100);
  });

  it('get a absolute url', () => {
    window.blessing = { base_url: 'http://localhost' };
    const url = require(modulePath).url;

    expect(url()).toBe('http://localhost/');
    expect(url('test')).toBe('http://localhost/test');
    expect(url('/test')).toBe('http://localhost/test');
  });
});
