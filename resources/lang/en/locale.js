(function ($) {
    'use strict';

    $.locales['en'] = {
        auth: {
            // Login
            emptyIdentification: 'Empty email/player name.',
            emptyPassword: 'Password is required.',
            emptyCaptcha: 'Please enter the CAPTCHA.',
            login: 'Log In',
            loggingIn: 'Logging In',
            tooManyFails: 'You fails too many times! Please enter the CAPTCHA.',

            // Register
            emptyEmail: 'Empty email address.',
            invalidEmail: 'Invalid format of email address.',
            invalidPassword: 'Invalid password. The length of password should between 8 and 32.',
            emptyConfirmPwd: 'Empty confirming password.',
            invalidConfirmPwd: 'Confirming password is not equal with password.',
            emptyNickname: 'Empty nickname.',
            emptyPlayerName: 'Empty player name.',
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
            applyNotice: 'You can apply it to player at your closet',
            emptyItemName: 'Empty texture name.',

            // Rename
            setNewTextureName: 'Please enter the new texture name:',
            emptyNewTextureName: 'Empty new texture name.',

            // Change Model
            setNewTextureModel: 'Please select a new texture model:',

            // Skinlib
            filter: {
                skin: '(Any Model)',
                steve: '(Steve)',
                alex: '(Alex)',
                cape: '(Cape)',
                uploader: 'User (UID = :uid) Uploaded',
                allUsers: 'All Users'
            },
            sort: {
                time: 'Newestly Uploaded',
                likes: 'Most Likes'
            },

            // Preview
            badSkinSize: 'The size of selected skin file is not valid',
            badCapeSize: 'The size of selected cape file is not valid',

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

            deleteNotice: 'Are you sure to delete this texture?'
        },
        user: {
            // Signing in
            signRemainingTime: 'Available after :time :unit',
            timeUnitHour: 'h',
            timeUnitMin: 'min',

            // Closet
            emptyClosetMsg: '<p>Nothing in your closet...</p><p>Why not explore the <a href=":url">Skin Library</a> for a while?</p>',
            renameItem: 'Rename item',
            removeItem: 'Remove from closet',
            setAsAvatar: 'Set as avatar',
            viewInSkinlib: 'View in skin library',
            switch2dPreview: 'Switch to 2D preview',
            switch3dPreview: 'Switch to 3D preview',
            removeFromClosetNotice: 'Sure to remove this texture from your closet?',
            emptySelectedPlayer: 'No player is selected.',
            emptySelectedTexture: 'No texture is selected.',
            renameClosetItem: 'Set a new name for this item:',

            // Player
            changePlayerName: 'Please enter the player name:',
            emptyPlayerName: 'Empty player name.',
            clearTexture: 'Sure to clear the skins & cape of this player?',
            deletePlayer: 'Sure to delete this player?',
            deletePlayerNotice: 'It\'s permanent. No backups.',
            chooseClearTexture: 'Choose texture types you want to clear',
            noClearChoice: 'You haven\'t choose any types',

            // Profile
            setAvatar: 'Sure to set this as your avatar?',
            setAvatarNotice: 'The head segment of skin will bu used.',
            emptyNewNickName: 'Empty new nickname.',
            changeNickName: 'Sure to set your nickname to :new_nickname?',
            emptyPassword: 'Original password is required.',
            emptyNewPassword: 'Empty new password.',
            emptyNewEmail: 'Empty new email address.',
            changeEmail: 'Sure to change your email address to :new_email?',
            emptyDeletePassword: 'Please enter the current password:'
        },
        admin: {
            operationsTitle: 'Operations',

            // Users
            ban: 'Ban',
            unban: 'Unban',
            setAdmin: 'Set as admin',
            unsetAdmin: 'Remove admin',
            deleteUser: 'Delete user',
            cannotDeleteAdmin: 'You can\'t delete admins.',
            cannotDeleteSuperAdmin: 'You can\'t delete super admins.',
            changeEmail: 'Edit email',
            changeNickName: 'Edit nickname',
            changePassword: 'Edit password',
            changeVerification: 'Switch verification status',
            newUserEmail: 'Please enter the new email:',
            newUserNickname: 'Please enter the new nickname:',
            newUserPassword: 'Please enter the new password:',
            deleteUserNotice: 'Are you sure to delete this user? It\'s permanent.',
            scoreTip: 'Press ENTER to submit new score',
            inspectHisOwner: 'Click to inspect the owner of this player',
            inspectHisPlayers: 'Click to inspect the players he owns',

            // Status
            banned: 'Banned',
            normal: 'Normal',
            admin: 'Admin',
            superAdmin: 'Super Admin',

            // Verification
            unverified: 'Unverified',
            verified: 'Verified',

            // Players
            textureType: 'Texture Type',
            skin: 'Skin (:model Model)',
            cape: 'Cape',
            pid: 'Texture ID',
            pidNotice: 'Please enter the tid of texture. Inputting 0 can clear texture of this player.',
            changePlayerTexture: 'Change textures of :player',
            changeTexture: 'Change textures',
            changePlayerName: 'Change player name',
            changeOwner: 'Change owner',
            deletePlayer: 'Delete',
            changePlayerOwner: 'Please enter the id of user which this player is transfering to:',
            deletePlayerNotice: 'Are you sure to delete this player? It\' permanent.',
            targetUser: 'Target user is :nickname',
            noSuchUser: 'No such user.',
            changePlayerNameNotice: 'Please input new player name:',
            emptyPlayerName: 'Player name cannot be empty.',

            // Plugins
            pluginTitle: 'Plugin',
            pluginAuthor: 'Author',
            pluginVersion: 'Version',
            pluginName: 'Name',
            pluginOperations: 'Operations',
            pluginDescription: 'Description',
            pluginDependencies: 'Dependencies',
            pluginEnabled: 'Enabled',
            enablePlugin: 'Enable',
            disablePlugin: 'Disable',
            configurePlugin: 'Configure',
            installPlugin: 'Install',
            pluginInstalling: 'Installing...',
            updatePlugin: 'Update',
            pluginUpdating: 'Updating...',
            confirmUpdate: 'Are you sure to update ":plugin" from :old to :new?',
            deletePlugin: 'Delete',
            confirmDeletion: 'Are you sure to delete this plugin?',
            noDependencies: 'No Dependencies',
            whyDependencies: 'What\'s this?',
            noDependenciesNotice: 'There is no dependency definition in the plugin. It means that the plugin may be not compatible with the current version of Blessing Skin, and enabling it may cause unexpected problems. Do you really want to enable the plugin?',

            // Update
            preparing: 'Preparing',
            downloadCompleted: 'Update package download completed.',
            extracting: 'Extracting update package...'
        },
        general: {
            skin: 'Skin',
            cape: 'Cape',
            fatalError: 'Fatal Error (Please contact the author)',
            confirmLogout: 'Sure to log out?',
            confirm: 'OK',
            cancel: 'Cancel',
            more: 'More',
            pagination: 'Page :page, total :total',
            searchResult: '(Search result of keyword ":keyword")',
            noResult: 'No result.'
        }
    };
})(window.jQuery);
