/* eslint no-unused-vars: "off" */

const $ = require('jquery');
window.$ = window.jQuery = $;

window.blessing = {
    base_url: '/'
};

describe('tests for "captcha" module', () => {
  it('refresh captcha', async () => {
    const url = jest.fn(path => path);
    window.url = url;

    document.body.innerHTML = `
      <img class="captcha" src="" />
      <input id="captcha" value="old" />
    `;

    require('../auth/captcha')();

    expect($('.captcha').attr('src')).toEqual(expect.stringContaining('auth/captcha?'));
    expect($('#captcha').val()).toBe('');
  });
});

describe('tests for "login" module', () => {
  const modulePath = '../auth/login';

  it('login', async () => {
    const fetch = jest.fn()
      .mockImplementationOnce(option => {
        option.beforeSend();
        return Promise.resolve({ errno: 0, msg: 'success' });
      })
      .mockImplementationOnce(() => Promise.resolve(
        { errno: 1, msg: 'warning1', login_fails: 1 }
      ))
      .mockImplementationOnce(() => Promise.resolve(
        { errno: 1, msg: 'warning2', login_fails: 4 }
      ))
      .mockImplementationOnce(() => Promise.reject());
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const swal = jest.fn();
    const refreshCaptcha = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.swal = swal;
    window.showMsg = jest.fn();
    window.refreshCaptcha = refreshCaptcha;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <input id="identification" />
      <input id="password" />
      <div id="captcha-form"></div>
      <input id="captcha" />
      <input id="keep" checked />
      <button id="login-button"></button>
    `;

    require(modulePath);

    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyIdentification');
    expect($('#identification').is(':focus')).toBe(true);

    $('#identification').val('username');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyPassword');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('password');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyCaptcha');
    expect($('#captcha').is(':focus')).toBe(true);

    $('#captcha').val('captcha');
    await $('button').click();
    expect(fetch).toBeCalledWith(expect.objectContaining({
      type: 'POST',
      url: 'auth/login',
      dataType: 'json',
      data: {
        identification: 'username',
        password: 'password',
        keep: true,
        captcha: 'captcha'
      }
    }));
    expect($('button').html()).toBe(
      '<i class="fa fa-spinner fa-spin"></i> auth.loggingIn'
    );
    expect($('button').prop('disabled')).toBe(true);
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });

    $('#captcha-form').css('display', 'none');
    await $('button').click();
    expect($('#captcha-form').css('display')).toBe('none');
    expect(refreshCaptcha).toBeCalled();
    expect(showMsg).toBeCalledWith('warning1', 'warning');
    expect($('button').html()).toBe('auth.login');
    expect($('button').prop('disabled')).toBe(false);

    await $('button').click();
    expect(swal).toBeCalledWith({ type: 'error', html: 'auth.tooManyFails' });
    expect($('#captcha-form').css('display')).not.toBe('none');
    expect(showMsg).toBeCalledWith('warning2', 'warning');

    await $('button').click();
    expect(showAjaxError).toBeCalled();
  });
});

describe('tests for "register" module', () => {
  const modulePath = '../auth/register';

  it('register', async () => {
    const fetch = jest.fn()
      .mockImplementationOnce(option => {
        option.beforeSend();
        return Promise.resolve({ errno: 0, msg: 'success' });
      })
      .mockImplementationOnce(() => Promise.resolve(
        { errno: 1, msg: 'warning' }
      ))
      .mockImplementationOnce(() => Promise.reject(new Error));
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const swal = jest.fn().mockImplementation(() => Promise.resolve());
    const showMsg = jest.fn();
    const refreshCaptcha = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.swal = swal;
    window.showMsg = showMsg;
    window.refreshCaptcha = refreshCaptcha;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <input id="email" />
      <input id="nickname" />
      <input id="password" />
      <input id="confirm-pwd" />
      <div id="captcha-form"></div>
      <input id="captcha" />
      <button id="register-button"></button>
    `;

    require(modulePath);

    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyEmail');
    expect($('#email').is(':focus')).toBe(true);

    $('#email').val('email');
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidEmail');
    expect(showMsg).toBeCalledWith('auth.invalidEmail', 'warning');
    expect($('#email').is(':focus')).toBe(true);

    $('#email').val('a@b.c');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyPassword');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('secret');
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidPassword');
    expect(showMsg).toBeCalledWith('auth.invalidPassword', 'warning');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('too_long_password_very_super_long');
    $('#password').blur();
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidPassword');
    expect(showMsg).toBeCalledWith('auth.invalidPassword', 'warning');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('password');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyConfirmPwd');
    expect($('#confirm-pwd').is(':focus')).toBe(true);

    $('#confirm-pwd').val('not_same');
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidConfirmPwd');
    expect(showMsg).toBeCalledWith('auth.invalidConfirmPwd', 'warning');
    expect($('#confirm-pwd').is(':focus')).toBe(true);

    $('#confirm-pwd').val('password');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyNickname');
    expect($('#nickname').is(':focus')).toBe(true);

    $('#nickname').val('nickname');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyCaptcha');
    expect($('#captcha').is(':focus')).toBe(true);

    $('#captcha').val('captcha');
    await $('button').click();
    expect(fetch).toBeCalledWith(expect.objectContaining({
      type: 'POST',
      url: 'auth/register',
      dataType: 'json',
      data: {
        email: 'a@b.c',
        nickname: 'nickname',
        password: 'password',
        captcha: 'captcha'
      }
    }));
    expect($('button').html()).toBe(
      '<i class="fa fa-spinner fa-spin"></i> auth.registering'
    );
    expect($('button').prop('disabled')).toBe(true);
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });

    await $('button').click();
    expect(refreshCaptcha).toBeCalled();
    expect(showMsg).toBeCalledWith('warning', 'warning');
    expect($('button').html()).toBe('auth.register');

    await $('button').click();
    expect(showAjaxError).toBeCalled();
  });
});

jest.useFakeTimers();

describe('tests for "forgot" module', () => {
  const modulePath = '../auth/forgot';

  it('forgot password', async () => {
    const fetch = jest.fn()
      .mockImplementationOnce(option => {
        option.beforeSend();
        return Promise.resolve({ errno: 0, msg: 'success' });
      })
      .mockImplementationOnce(() => Promise.resolve(
        { errno: 1, msg: 'warning' }
      ))
      .mockImplementationOnce(() => Promise.reject(new Error));
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const swal = jest.fn();
    const showMsg = jest.fn();
    const refreshCaptcha = jest.fn();
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.swal = swal;
    window.showMsg = showMsg;
    window.refreshCaptcha = refreshCaptcha;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <input id="email" />
      <div id="captcha-form"></div>
      <input id="captcha" />
      <button id="forgot-button" data-remain="60"></button>
    `;

    require(modulePath)();
    expect(setInterval).toHaveBeenCalledTimes(1);
    jest.runTimersToTime(1000);

    $('button').click();
    expect($('button').prop('disabled')).toBe(true);
    expect(fetch).not.toBeCalled();

    jest.runTimersToTime(60000);
    expect($('button').html()).toBe('auth.send');
    expect($('button').prop('disabled')).toBe(false);

    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyEmail');
    expect($('#email').is(':focus')).toBe(true);

    $('#email').val('email');
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidEmail');
    expect(showMsg).toBeCalledWith('auth.invalidEmail', 'warning');
    expect($('#email').is(':focus')).toBe(true);

    $('#email').val('a@b.c');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyCaptcha');
    expect($('#captcha').is(':focus')).toBe(true);

    $('#captcha').val('captcha');
    await $('button').click();
    expect(fetch).toBeCalledWith(expect.objectContaining({
      type: 'POST',
      url: 'auth/forgot',
      dataType: 'json',
      data: {
        email: 'a@b.c',
        captcha: 'captcha'
      }
    }));
    expect($('button').html()).toEqual(expect.stringContaining('auth.send'));
    expect($('button').prop('disabled')).toBe(true);
    expect(showMsg).toBeCalledWith('success', 'success');

    await $('button').click();
    expect(refreshCaptcha).toBeCalled();
    expect(showMsg).toBeCalledWith('warning', 'warning');
    expect($('button').html()).toBe('auth.send');

    await $('button').click();
    expect($('button').html()).toBe('auth.send');
    expect($('button').prop('disabled')).toBe(false);
    expect(showAjaxError).toBeCalled();
  });
});

jest.useRealTimers();

describe('tests for "reset" module', () => {
  const modulePath = '../auth/reset';

  it('reset password', async () => {
    const fetch = jest.fn()
      .mockImplementationOnce(option => {
        option.beforeSend();
        return Promise.resolve({ errno: 0, msg: 'success' });
      })
      .mockImplementationOnce(() => Promise.resolve(
        { errno: 1, msg: 'warning' }
      ))
      .mockImplementationOnce(() => Promise.reject(new Error));
    const trans = jest.fn(key => key);
    const url = jest.fn(path => path);
    const swal = jest.fn().mockReturnValue(Promise.resolve());
    const showMsg = jest.fn();
    const getQueryString = jest.fn().mockReturnValue('token');
    const showAjaxError = jest.fn();
    window.fetch = fetch;
    window.trans = trans;
    window.url = url;
    window.swal = swal;
    window.showMsg = showMsg;
    window.refreshCaptcha = jest.fn();
    window.getQueryString = getQueryString;
    window.showAjaxError = showAjaxError;

    document.body.innerHTML = `
      <input id="uid" value="1" />
      <input id="password" />
      <input id="confirm-pwd" />
      <button id="reset-button"></button>
    `;

    require(modulePath);

    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyPassword');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('secret');
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidPassword');
    expect(showMsg).toBeCalledWith('auth.invalidPassword', 'warning');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('too_long_password_very_super_long');
    $('#password').blur();
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidPassword');
    expect(showMsg).toBeCalledWith('auth.invalidPassword', 'warning');
    expect($('#password').is(':focus')).toBe(true);

    $('#password').val('password');
    $('button').click();
    expect(trans).toBeCalledWith('auth.emptyConfirmPwd');
    expect($('#confirm-pwd').is(':focus')).toBe(true);

    $('#confirm-pwd').val('not_same');
    $('button').click();
    expect(trans).toBeCalledWith('auth.invalidConfirmPwd');
    expect(showMsg).toBeCalledWith('auth.invalidConfirmPwd', 'warning');
    expect($('#confirm-pwd').is(':focus')).toBe(true);

    $('#confirm-pwd').val('password');
    await $('button').click();
    expect(getQueryString).toBeCalledWith('token');
    expect(fetch).toBeCalledWith(expect.objectContaining({
      type: 'POST',
      url: 'auth/reset',
      dataType: 'json',
      data: {
        uid: '1',
        password: 'password',
        token: 'token'
      }
    }));
    expect($('button').html()).toBe(
      '<i class="fa fa-spinner fa-spin"></i> auth.resetting'
    );
    expect($('button').prop('disabled')).toBe(true);
    expect(swal).toBeCalledWith({ type: 'success', html: 'success' });

    await $('button').click();
    expect(showMsg).toBeCalledWith('warning', 'warning');
    expect($('button').html()).toBe('auth.reset');

    await $('button').click();
    expect($('button').html()).toBe('auth.reset');
    expect(showAjaxError).toBeCalled();
  });
});
