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

    $.locales['en'] = {
        auth: {
            // Login
            emptyIdentification: 'Empty email/player name.',
            emptyPassword: 'Password is required.',
            emptyCaptcha: 'Empty password.',
            login: 'Log In',
            loggingIn: 'Logging In',
            tooManyFails: 'You fails too many times! Please enter the CAPTCHA.',

            // Register
            emptyEmail: 'Empty email address.',
            invalidEmail: 'Invalid format of email address.',
            invalidPassword: 'Invalid password. The length of password should between 8 and 16.',
            emptyConfirmPwd: 'Empty confirming password.',
            invalidConfirmPwd: 'Confirming password is not equal with password.',
            emptyNickname: 'Empty nickname.',
            register: 'Register',
            registering: 'Registering',

            // Reset Password
            send: 'Send',
            sending: 'Sending',
            reset: 'Reset',
            resetting: 'Resetting'
        },
        skinlib: {
            // Like
            addToCloset: 'Add to closet',
            removeFromCloset: 'Remove from closet',
            setItemName: 'Set a name for this texture',
            emptyItemName: 'Empty texture name.',

            // Rename
            setNewTextureName: 'Please enter the new texture name:',
            emptyNewTextureName: 'Empty new texture name.',

            // Upload
            emptyTextureName: 'Empty texture name.',
            emptyTextureType: 'Please select a type for this texture.',
            emptyUploadFile: 'You have not uploaded any file.',
            encodingError: 'Error: Encoding of this file is not accepted.',
            fileExtError: 'Error: Textures should be PNG files.',
            upload: 'Upload',
            uploading: 'Uploading',
            redirecting: 'Redirecting...',

            // Change Privacy
            setAsPrivate: 'Set as private',
            setAsPublic: 'Set as public',
            setPublicNotice: 'Sure to set this as public texture?',

            deleteNotice: 'Are you sure to delete this texture? Scores will be returned.'
        },
        user: {
            checkinRemainTime: 'Available after :time hours',

            // Closet
            switch2dPreview: 'Switch to 2D Preview',
            switch3dPreview: 'Switch to 3D Preview',
            removeFromClosetNotice: 'Sure to remove this texture from your closet?',
            emptySelectedPlayer: 'No player is selected.',
            emptySelectedTexture: 'No texture is selected.',
            renameClosetItem: 'Set a new name for this item:',

            // Player
            changePlayerName: 'Please enter the player name:',
            playerNameRule: 'The player name should only contain letters, numbers, and dashes.',
            emptyPlayerName: 'Empty player name.',
            clearTexture: 'Sure to clear the skins & cape of this player?',
            deletePlayer: 'Sure to delete this player?',
            deletePlayerNotice: 'It\'s permanent. No backups.',

            // Profile
            setAvatar: 'Sure to set this as your avatar?',
            setAvatarNotice: 'The head segment of skin will bu used.',
            emptyNewNickName: 'Empty new nickname.',
            changeNickName: 'SUre to set your nickname to :new_nickname?',
            emptyPassword: 'Original password is required.',
            emptyNewPassword: 'Empty new password.',
            emptyNewEmail: 'Empty new email address.',
            changeEmail: 'Sure to change your email address to :new_email?',
            emptyDeletePassword: 'Please enter the current password:'
        },
        config: {
            csl13_1Upper: 'v13.1 and upper (recommended)',
            csl13_1Lower: 'lower than v13.1',
            usm1_4Upper:  'v1.4 and upper (recommended)',
            usm1_2To1_3:  'v1.2 to v1.3',
            usm1_2Lower:  'lower than v1.2',
        },
        admin: {
            // Change User Profile
            newUserEmail: 'Please enter the new email:',
            newUserNickname: 'Please enter the new nickname:',
            newUserPassword: 'Please enter the new password:',
            deleteUserNotice: 'Are you sure to delete this user? It\' permanent.',
            changePlayerOwner: 'Please enter the id of user which this player should be transferred to:',
            deletePlayerNotice: 'Are you sure to delete this player? It\' permanent.',

            // Status
            banned: 'Banned',
            normal: 'Normal',
            admin:  'Admin',

            // Operations
            ban: 'Ban',
            unban: 'Unban',
            setAdmin: 'Set as admin',
            unsetAdmin: 'Remove admin',

            // Change Player Texture
            textureType: 'Texture Type',
            skin: 'Skin (:model Model)',
            cape: 'Cape',
            pid: 'Texture ID',
            pidNotice: 'Please enter the tid of texture',
            changePlayerTexture: 'Change textures of :player',

            // Index
            textureUploads: 'Texture Uploads',
            userRegistration: 'User Registration',

            // Plugins
            statusEnabled: 'Enabled',
            statusDisabled: 'Disabled',
            enablePlugin: 'Enable',
            disablePlugin: 'Disable',
            confirmDeletion: 'Are you sure to delete this plugin?'
        },
        utils: {
            fatalError: 'Fatal Error (Please contact the author)'
        },
        general: {
            confirmLogout: 'Sure to log out?',
            confirm: 'OK',
            cancel: 'Cancel'
        }
    };
})(window.jQuery);
