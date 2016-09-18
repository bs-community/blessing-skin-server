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
        skinlib: {
            setSkinName: '给你的皮肤起个名字吧~',
            removeFromCloset: '从衣柜中移除',
            addToCloset: '添加至衣柜',
            encodingError: '错误：这张图片编码不对哦',
            formatError: '错误：皮肤文件必须为 PNG 格式',
            chooseTextureType: '请选择材质的类型',
            noUploadFile: '你还没有上传任何文件哦',
            setTextureName: '给你的材质起个名字吧',
            choosePNG: '请选择 PNG 格式的图片',
            uploading: '上传中',
            redirecting: '正在跳转...',
            confirmUpload: '确认上传',
            inputTextureName: '请输入新的材质名称：',
            warningPublic: '要将此材质设置为公开吗？',
            setPrivate: '设为隐私',
            setPublic: '设为公开',
            warningDelete: '真的要删除此材质吗？积分将会被返还'
        },
        user: {
            switch2dPreview: '切换 2D 预览',
            switch3dPreview: '切换 3D 预览',
            removeFromCloset: '确定要从衣柜中移除此材质吗？',
            setAvatar: '确定要将此材质设置为用户头像吗？',
            setAvatarNotice: '将会自动截取皮肤头部',
            noSelectedPlayer: '你还没有选择角色哦',
            noSelectedTexture: '你还没有选择要应用的材质哦',
            changePlayerName: '请输入角色名：',
            playerNameRule: '允许数字、字母以及下划线，是否支持中文角色名请参考本站设置',
            emptyPlayerName: '你还没有填写名称哦',
            clearTexture: '确定要重置该用户的皮肤/披风吗？',
            deletePlayer: '真的要删除该玩家吗？',
            deletePlayerNotice: '这将是永久性的删除',
            emptyNewNickName: '你还没有填写新昵称啊',
            changeNickName: '确定要将昵称设置为 :new_nickname 吗？',
            emptyPassword: '原密码不能为空',
            emptyNewPassword: '新密码要好好填哦',
            emptyNewEmail: '你还没有填写新邮箱啊',
            changeEmail: '确定要将用户邮箱更改为 :new_email 吗？',
            emptyDeletePassword: '请先输入当前用户密码',
            signRemainTime: ':time 小时后可签到'
        },
        utils: {
            fatalError: '严重错误（请联系作者）'
        }
        config: {
            csl13_1Upper: '13.1 版及以上（推荐）',
            csl13_1Lower: '13.1 版以下',
            usm1_4Upper:  '1.4 版及以上（推荐）',
            usm1_2To1_3:  '1.2 及 1.3 版',
            usm1_2Lower:  '1.2 版以下',
        },
        general: {
            confirmLogout: '确定要登出吗？',
            confirm: '确定',
            cancel: '取消'
        }
    };
})(window.jQuery);
