/* global MSP */

'use strict';

$('body')
    .on('change', '#file', () => handleFiles())
    .on('ifToggled', '#type-cape', () => {
        MSP.clear();
        handleFiles();
    })
    .on('ifToggled', '#type-skin', function () {
        $(this).prop('checked') ? $('#skin-type').show() : $('#skin-type').hide();
    })
    .on('ifToggled', '#private', function () {
        $(this).prop('checked') ? $('#msg').show() : $('#msg').hide();
    });

// Real-time preview
function handleFiles(files, type) {

    files = files || $('#file').prop('files');
    type  = type  || $('#type-cape').prop('checked') ? 'cape' : 'skin';

    if (files.length > 0) {
        let file = files[0];

        if (file.type === 'image/png' || file.type === 'image/x-png') {
            let reader = new FileReader();

            reader.onload = function () {
                let img = new Image();

                img.onload = () => {
                    let $name = $('#name');

                    (type == 'skin') ? MSP.changeSkin(img.src) : MSP.changeCape(img.src);

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
    let form = new FormData();
    let file = $('#file').prop('files')[0];

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
        } else if ($('#name').val() == '') {
            toastr.info(trans('skinlib.emptyTextureName'));
            $('#name').focus();
        } else if (file.type !== 'image/png') {
            toastr.warning(trans('skinlib.fileExtError'));
            $('#file').focus();
        } else {
            callback();
        }
    })(form, file, () => {
        fetch({
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
        }).then(({ errno, msg, tid }) => {
            if (errno == 0) {
                let redirect = function () {
                    toastr.info(trans('skinlib.redirecting'));

                    window.setTimeout(() => {
                        window.location = url(`skinlib/show/${tid}`);
                    }, 1000);
                };

                // Always redirect
                swal({
                    type: 'success',
                    html: msg
                }).then(redirect, redirect);
            } else {
                swal({
                    type: 'warning',
                    html: msg
                }).then(() => {
                    $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
                });
            }
        }).catch(err => {
            showAjaxError(err);
            $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
        });
    });
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        upload
    };
}
