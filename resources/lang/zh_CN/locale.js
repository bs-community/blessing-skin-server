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

    $.locales['zh_CN'] = {
        auth: {
            // Login
            emptyIdentification: '你还没有填写邮箱/角色名哦',
            emptyPassword: '密码要好好填哦',
            emptyCaptcha: '你还没有填写验证码哦',
            login: '登录',
            loggingIn: '登录中',
            tooManyFails: '你尝试的次数太多啦，请输入验证码',

            // Register
            emptyEmail: '你还没有填写邮箱哦',
            invalidEmail: '邮箱格式不正确！',
            invalidPassword: '无效的密码。密码长度应该大于 8 并小于 16。',
            emptyConfirmPwd: '确认密码不能为空',
            invalidConfirmPwd: '密码和确认的密码不一样诶？',
            emptyNickname: '你还没有填写昵称哦',
            register: '注册',
            registering: '注册中',

            // Reset Password
            send: '发送',
            sending: '发送中',
            reset: '重置',
            resetting: '重置中'
        },
        skinlib: {
            // Like
            addToCloset: '添加至衣柜',
            removeFromCloset: '从衣柜中移除',
            setItemName: '给你的皮肤起个名字吧~',
            emptyItemName: '你还没有填写要收藏的材质名称啊',

            // Rename
            setNewTextureName: '请输入新的材质名称：',
            emptyNewTextureName: '你还没有输入新名称啊',

            // Upload
            emptyTextureName: '给你的材质起个名字吧',
            emptyTextureType: '请选择材质的类型',
            emptyUploadFile: '你还没有上传任何文件哦',
            encodingError: '错误：这张图片编码不对哦',
            fileExtError: '错误：皮肤文件必须为 PNG 格式',
            upload: '确认上传',
            uploading: '上传中',
            redirecting: '正在跳转...',

            // Change Privacy
            setAsPrivate: '设为隐私',
            setAsPublic: '设为公开',
            setPublicNotice: '要将此材质设置为公开吗？',

            deleteNotice: '真的要删除此材质吗？积分将会被返还'
        },
        user: {
            checkinRemainTime: ':time 小时后可签到',

            // Closet
            switch2dPreview: '切换 2D 预览',
            switch3dPreview: '切换 3D 预览',
            removeFromClosetNotice: '确定要从衣柜中移除此材质吗？',
            emptySelectedPlayer: '你还没有选择角色哦',
            emptySelectedTexture: '你还没有选择要应用的材质哦',
            renameClosetItem: '请输入此衣柜物品的新名称：',

            // Player
            changePlayerName: '请输入角色名：',
            playerNameRule: '允许数字、字母以及下划线，是否支持中文角色名请参考本站设置',
            emptyPlayerName: '你还没有填写名称哦',
            clearTexture: '确定要重置该用户的皮肤/披风吗？',
            deletePlayer: '真的要删除该玩家吗？',
            deletePlayerNotice: '这将是永久性的删除',

            // Profile
            setAvatar: '确定要将此材质设置为用户头像吗？',
            setAvatarNotice: '将会自动截取皮肤头部',
            emptyNewNickName: '你还没有填写新昵称啊',
            changeNickName: '确定要将昵称设置为 :new_nickname 吗？',
            emptyPassword: '原密码不能为空',
            emptyNewPassword: '新密码要好好填哦',
            emptyNewEmail: '你还没有填写新邮箱啊',
            changeEmail: '确定要将用户邮箱更改为 :new_email 吗？',
            emptyDeletePassword: '请先输入当前用户密码'
        },
        config: {
            csl13_1Upper: '13.1 版及以上（推荐）',
            csl13_1Lower: '13.1 版以下',
            usm1_4Upper:  '1.4 版及以上（推荐）',
            usm1_2To1_3:  '1.2 及 1.3 版',
            usm1_2Lower:  '1.2 版以下',
        },
        admin: {
            // Change User Profile
            newUserEmail: '请输入新邮箱：',
            newUserNickname: '请输入新昵称：',
            newUserPassword: '请输入新密码：',
            deleteUserNotice: '真的要删除此用户吗？此操作不可恢复',
            changePlayerOwner: '请输入此角色要让渡至的用户 UID：',
            deletePlayerNotice: '真的要删除此角色吗？此操作不可恢复',

            // Status
            banned: '封禁',
            normal: '正常',
            admin:  '管理员',

            // Operations
            ban: '封禁',
            unban: '解封',
            setAdmin: '设为管理员',
            unsetAdmin: '解除管理员',

            // Change Player Texture
            textureType: '材质类型',
            skin: '皮肤（:model 模型）',
            cape: '披风',
            pid: '材质 ID',
            pidNotice: '输入要更换的材质的 TID',
            changePlayerTexture: '更换角色 :player 的材质',

            // Index
            textureUploads: '材质上传',
            userRegistration: '用户注册',

            // Plugins
            confirmDeletion: '真的要删除这个插件吗？'
        },
        utils: {
            fatalError: '严重错误（请联系作者）'
        },
        general: {
            confirmLogout: '确定要登出吗？',
            confirm: '确定',
            cancel:  '取消'
        },
        vendor: {
            datatables: {
                "sProcessing": "处理中...",
                "sLengthMenu": "每页 _MENU_ 项",
                "sZeroRecords": "没有匹配结果",
                "sInfo": "当前显示第 _START_ 至 _END_ 项，共 _TOTAL_ 项。",
                "sInfoEmpty": "当前显示第 0 至 0 项，共 0 项",
                "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                "sInfoPostFix": "",
                "sSearch": "搜索:",
                "sUrl": "",
                "sEmptyTable": "表中数据为空",
                "sLoadingRecords": "载入中...",
                "sInfoThousands": ",",
                "oPaginate": {
                    "sFirst": "首页",
                    "sPrevious": "上页",
                    "sNext": "下页",
                    "sLast": "末页",
                    "sJump": "跳转"
                },
                "oAria": {
                    "sSortAscending": ": 以升序排列此列",
                    "sSortDescending": ": 以降序排列此列"
                }
            }
        }
    };
})(window.jQuery);
