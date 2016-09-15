/*!
 * Blessing Skin Chinese Translations
 *
 * @see https://github.com/printempw/blessing-skin-server
 * @author printempw <h@prinzeugen.net>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */

(function ($) {
    "use strict";

    $.locales['zh-CN'] = {
        auth: {
            emptyIdentification: '你还没有填写邮箱/角色名哦',
            emptyPassword: '密码要好好填哦',
            emptyCaptcha: '你还没有填写验证码哦',
            login: '登录',
            loggingIn: '登录中',
            tooManyFails: '你尝试的次数太多啦，请输入验证码',
            emptyEmail: '你还没有填写邮箱哦',
            invalidEmail: '邮箱格式不正确！',
            invalidPassword: '无效的密码。密码长度应该大于 8 并小于 16。',
            emptyConfirmPwd: '确认密码不能为空',
            invalidConfirmPwd: '密码和确认的密码不一样诶？',
            emptyNickname: '你还没有填写昵称哦',
            register: '注册',
            registering: '注册中',
            send: '发送',
            sending: '发送中',
            reset: '重置',
            resetting: '重置中'
        },
        general: {
            confirmLogout: '确定要登出吗？',
            confirm: '确定',
            cancel: '取消'
        }
    };
})(window.jQuery);
