/* global initSkinViewer, defaultSkin */

// TODO: Help wanted. This file needs to be tested.

'use strict';

function initUploadListeners() {
    if (trans('vendor.fileinput') !== 'vendor.fileinput') {
        $.fn.fileinputLocales[blessing.locale] = trans('vendor.fileinput');
    }

    $('body')
        .on('change', ['#file', '#skin-type'], () => handleFiles())
        .on('ifToggled', '#type-cape', () => handleFiles())
        .on('ifToggled', '#type-skin', function () {
            $(this).prop('checked') ? $('#skin-type').show() : $('#skin-type').hide();
        })
        .on('ifToggled', '#private', function () {
            $(this).prop('checked') ? $('#msg').show() : $('#msg').hide();
        });

    $(document).ready(() => {
        $.msp.config.skinUrl = defaultSkin;
        initSkinViewer();
        $('[for="private"]').tooltip();
        $.msp.handles.walk.paused = $.msp.handles.rotate.paused = false;
    });

    $(window).resize(() => {
        $.msp.viewer.width  = $('#skin_container').width();
        $.msp.viewer.height = $('#skin_container').height();
    });
}

// Real-time preview
function handleFiles(files, type) {

    files = files || $('#file').prop('files');
    type  = type  || $('#type-cape').prop('checked') ? 'cape' : 'skin';

    if (files.length > 0) {
        const file = files[0];

        if (file.type === 'image/png' || file.type === 'image/x-png') {
            const reader = new FileReader();

            reader.onload = function () {
                const img = new Image();

                img.onload = () => {
                    const $name = $('#name');

                    if (type === 'cape') {
                        $.msp.config.skinUrl = defaultSkin;

                        if (img.width / img.height === 2) {
                            $.msp.config.capeUrl = img.src;
                        } else {
                            $.msp.config.capeUrl = null;
                            toastr.warning(trans('skinlib.badCapeSize'));
                        }
                    } else {
                        // Check skin size
                        if (img.width === img.height || img.width / img.height === 2) {
                            $.msp.config.skinUrl = img.src;
                            $.msp.config.capeUrl = null;
                        } else {
                            $.msp.config.skinUrl = defaultSkin;
                            toastr.warning(trans('skinlib.badSkinSize'));
                        }
                    }

                    $.msp.config.slim = ($('#skin-type').val() === 'alex');

                    initSkinViewer();

                    $.msp.handles.walk.paused = $.msp.handles.rotate.paused = false;

                    if ($name.val() === '' || $name.val() === $name.attr('data-last-file-name')) {
                        // Remove png extension in filename
                        const fileName = file.name.replace(/\.[Pp][Nn][Gg]$/, '');

                        $name.attr('data-last-file-name', fileName);
                        $name.val(fileName);
                    }
                };

                img.onerror = () => toastr.warning(trans('skinlib.fileExtError'));

                img.src = this.result;
            };

            reader.readAsDataURL(file);
        } else {
            toastr.warning(trans('skinlib.encodingError'));
        }
    }
}

function upload() {
    const form = new FormData();
    const file = $('#file').prop('files')[0];

    form.append('name',   $('#name').val());
    form.append('file',   file);
    form.append('public', ! $('#private').prop('checked'));

    if ($('#type-skin').prop('checked')) {
        form.append('type', $('#skin-type').val());
    } else if ($('#type-cape').prop('checked')) {
        form.append('type', 'cape');
    } else {
        return toastr.info(trans('skinlib.emptyTextureType'));
    }

    (function validate(form, file, callback) {
        if (file === undefined) {
            toastr.info(trans('skinlib.emptyUploadFile'));
            $('#file').focus();
        } else if ($('#name').val() === '') {
            toastr.info(trans('skinlib.emptyTextureName'));
            $('#name').focus();
        } else if (file.type !== 'image/png') {
            toastr.warning(trans('skinlib.fileExtError'));
            $('#file').focus();
        } else {
            callback();
        }
    })(form, file, async () => {
        try {
            const { errno, msg, tid } = await fetch({
                type: 'POST',
                url: url('skinlib/upload'),
                contentType: false,
                dataType: 'json',
                data: form,
                processData: false,
                beforeSend: () => {
                    $('#upload-button').html(
                        '<i class="fa fa-spinner fa-spin"></i> ' + trans('skinlib.uploading')
                    ).prop('disabled', 'disabled');
                }
            });

            if (errno === 0) {
                const redirect = function () {
                    toastr.info(trans('skinlib.redirecting'));

                    setTimeout(() => {
                        window.location = url(`skinlib/show/${tid}`);
                    }, 1000);
                };

                // Always redirect
                swal({
                    type: 'success',
                    html: msg
                }).then(redirect, redirect);
            } else {
                await swal({
                    type: 'warning',
                    html: msg
                });
                $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
            }
        } catch (error) {
            showAjaxError(error);
            $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
        }
    });
}

if (process.env.NODE_ENV === 'test') {
    module.exports = { upload, initUploadListeners };
}
