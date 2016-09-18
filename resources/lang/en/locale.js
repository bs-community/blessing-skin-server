/*!
 * Blessing Skin English Translations
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
            emptyIdentification: 'Empty email/player name.',
            emptyPassword: 'Password is required.',
            emptyCaptcha: 'Empty password.',
            login: 'Log In',
            loggingIn: 'Logging In',
            tooManyFails: 'You fails too many times! Please enter the CAPTCHA.',
            emptyEmail: 'Empty email address.',
            invalidEmail: 'Invalid format of email address.',
            invalidPassword: 'Invalid password. The length of password should between 8 and 16.',
            emptyConfirmPwd: 'Empty confirming password.',
            invalidConfirmPwd: 'Confirming password is not equal with password.',
            emptyNickname: 'Empty nickname.',
            register: 'Register',
            registering: 'Registering',
            send: 'Send',
            sending: 'Sending',
            reset: 'Reset',
            resetting: 'Resetting'
        },
        skinlib: {
            setSkinName: 'Please name your skin~',
            removeFromCloset: 'Remove from closet.',
            addToCloset: 'Add to closet.',
            encodingError: 'Error: Encoding of this picture is incorrect.',
            formatError: 'Error: Format of skin file must be PNG.',
            chooseTextureType: 'Please choose type of texture.',
            noUploadFile: 'You have not uploaded any file.',
            setTextureName: 'Please name your texture.',
            choosePNG: 'Please choose PNG picture file.',
            uploading: 'Uploading',
            redirecting: 'Redirecting...',
            confirmUpload: 'Confirm uploading',
            inputTextureName: 'Please input new name of texture.',
            warningPublic: 'Are you sure to make this texture public?',
            setPrivate: 'Make it private',
            setPublic: 'Make it public',
            warningDelete: 'Are you sure to delete this texture? Scores will be returned.',
            
        },
        user: {
            switch2dPreview: 'Switch to 2D Preview',
            switch3dPreview: 'Switch to 3D Preview',
            removeFromCloset: 'Sure to remove this from your closet?',
            setAvatar: 'Sure to set this as your avatar?',
            setAvatarNotice: 'The head segment of skin will bu used.',
            noSelectedPlayer: 'No player is selected.',
            noSelectedTexture: 'No texture is selected.',
            changePlayerName: 'Please enter the player name:',
            playerNameRule: 'The player name may only contain letters, numbers, and dashes. Chinese characters supporting is depended on option of this site.',
            emptyPlayerName: 'Empty player name.',
            clearTexture: 'Sure to clear the skins & cape of this player?',
            deletePlayer: 'Sure to delete this player?',
            deletePlayerNotice: 'It\'s permanent. No backups.',
            emptyNewNickName: 'Empty new nickname.',
            changeNickName: 'SUre to set your nickname to :new_nickname?',
            emptyPassword: 'Original password is required.',
            emptyNewPassword: 'Empty new password.',
            emptyNewEmail: 'Empty new email address.',
            changeEmail: 'Sure to change your email address to :new_email?',
            emptyDeletePassword: 'Please enter the current password:',
            signRemainTime: 'Can sign after :time hours',
        },
        config: {
            csl13_1Upper: 'v13.1 and upper (recommended)',
            csl13_1Lower: 'lower than v13.1',
            usm1_4Upper:  'v1.4 and upper (recommended)',
            usm1_2To1_3:  'v1.2 to v1.3',
            usm1_2Lower:  'lower than v1.2',
        },
        general: {
            confirmLogout: 'Sure to log out?',
            confirm: 'OK',
            cancel: 'Cancel'
        }
    };
})(window.jQuery);
