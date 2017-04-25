/*
 * @Author: printempw
 * @Date:   2016-07-19 10:46:38
 * @Last Modified by: g-plane
 * @Last Modified time: 2017-04-25 21:56:19
 */

'use strict';

$(document).ready(() => swal.setDefaults({
    confirmButtonText: trans('general.confirm'),
    cancelButtonText: trans('general.cancel')
}));

$('#page-select').on('change', function () {
    let sort   = getQueryString('sort'),
        page   = getQueryString('page'),
        filter = getQueryString('filter');

    let targetPage = $(this).val();

    // if has query strings
    if (filter || sort) {

        if (page == null) {
            // append "&page=" to current URL
            window.location = `${location.href}&page=${targetPage}`;
        } else {
            window.location = `?filter=${filter}&sort=${sort}&page=${targetPage}`;
        }

    } else {
        window.location = `?page=${targetPage}`;
    }

});

$('#private').on('ifToggled', function () {
    $(this).prop('checked') ? $('#msg').show() : $('#msg').hide();
});
$('#type-skin').on('ifToggled', function () {
    $(this).prop('checked') ? $('#skin-type').show() : $('#skin-type').hide();
});

function addToCloset(tid) {
    $.getJSON(url(`skinlib/info/${tid}`), (json) => {
        swal({
            title: trans('skinlib.setItemName'),
            inputValue: json.name,
            input: 'text',
            showCancelButton: true,
            inputValidator: (value) => {
                return new Promise((resolve, reject) => {
                    value ? resolve() : reject(trans('skinlib.emptyItemName'));
                });
            }
        }).then((result) => ajaxAddToCloset(tid, result));
    });
}

/**
 * Update button action & likes of texture.
 *
 * @param  {int}    tid
 * @param  {string} action add|remove
 * @return {null}
 */
function updateTextureStatus(tid, action) {
    let likes  = parseInt($('#likes').html()) + (action == "add" ? 1 : -1);
        action = (action == "add") ? 'removeFromCloset' : 'addToCloset';

    $(`a[tid=${tid}]`).attr('href', `javascript:${action}(${tid});`).attr('title', trans('skinlib.' + action)).toggleClass('liked');
    $('#'+tid).attr('href', `javascript:${action}(${tid});`).html(trans('skinlib.' + action));
    $('#likes').html(likes);
}

function ajaxAddToCloset(tid, name) {
    // remove interference of modal which is hide
    $('.modal').each(function () {
        return ($(this).css('display') == "none") ? $(this).remove() : null;
    });

    $.ajax({
        type: "POST",
        url: url("user/closet/add"),
        dataType: "json",
        data: { 'tid': tid, 'name': name },
        success: (json) => {
            if (json.errno == 0) {
                swal({
                    type: 'success',
                    html: json.msg
                });

                $('.modal').modal('hide');
                updateTextureStatus(tid, 'add');
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function removeFromCloset(tid) {
    swal({
        text: trans('user.removeFromClosetNotice'),
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#3085d6',
        confirmButtonColor: '#d33'
    }).then(() => {
        $.ajax({
            type: "POST",
            url: url("/user/closet/remove"),
            dataType: "json",
            data: { 'tid' : tid },
            success: (json) => {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });

                    updateTextureStatus(tid, 'remove');
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });

}

$('body').on('change', '#file', () => handleFiles()).on('ifToggled', '#type-cape', () => {
    MSP.clear();
    handleFiles();
});

// Real-time preview
function handleFiles(files, type) {

    files = files || $('#file').prop('files');
    type  = type  || $('#type-cape').prop('checked') ? "cape" : "skin";

    if (files.length > 0) {
        let file = files[0];

        if (file.type === "image/png" || file.type === "image/x-png") {
            let reader = new FileReader();

            reader.onload = function (e) {
                let img = new Image();

                img.onload = () => {

                    (type == "skin") ? MSP.changeSkin(img.src) : MSP.changeCape(img.src);
                    let domTextureName = $('#name');
                    if (domTextureName.val() === '' || domTextureName.val() === domTextureName.attr('data-last-file-name')) {
                        const fileName = file.name.replace(/\.[Pp][Nn][Gg]$/, '');
                        domTextureName.attr('data-last-file-name', fileName);
                        domTextureName.val(fileName);
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
};

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

    if (file === undefined) {
        toastr.info(trans('skinlib.emptyUploadFile'));
        $('#file').focus();
    } else if ($('#name').val() == "") {
        toastr.info(trans('skinlib.emptyTextureName'));
        $('#name').focus();
    } else if (file.type !== "image/png") {
        toastr.warning(trans('skinlib.fileExtError'));
        $('#file').focus();
    } else {
        $.ajax({
            type: "POST",
            url: url("skinlib/upload"),
            contentType: false,
            dataType: "json",
            data: form,
            processData: false,
            beforeSend: () => {
                $('#upload-button').html('<i class="fa fa-spinner fa-spin"></i> ' + trans('skinlib.uploading')).prop('disabled', 'disabled');
            },
            success: (json) => {
                if (json.errno == 0) {
                    let redirect = function () {
                        toastr.info(trans('skinlib.redirecting'));

                        window.setTimeout(() => {
                            window.location = url(`skinlib/show/${json.tid}`);
                        }, 1000);
                    };

                    // always redirect
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(redirect, redirect);

                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    }).then(() => {
                        $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
                    });
                }
            },
            error: (json) => {
                $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
                showAjaxError(json);
            }
        });
    }
    return false;
}

function changeTextureName(tid, oldName) {
    swal({
        text: trans('skinlib.setNewTextureName'),
        input: 'text',
        inputValue: oldName,
        showCancelButton: true,
        inputValidator: (value) => {
            return new Promise((resolve, reject) => {
                (value) ? resolve() : reject(trans('skinlib.emptyNewTextureName'));
            });
        }
    }).then((new_name) => {
        $.ajax({
            type: "POST",
            url: url("skinlib/rename"),
            dataType: "json",
            data: { 'tid': tid, 'new_name': new_name },
            success: (json) => {
                if (json.errno == 0) {
                    $('#name').text(new_name);
                    toastr.success(json.msg);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}

$('.private-label').click(function () {
    swal({
        text: trans('skinlib.setPublicNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => {
        changePrivacy($(this).attr('tid'));
        $(this).remove();
    });
});

function changePrivacy(tid) {
    $.ajax({
        type: "POST",
        url: url(`skinlib/privacy`),
        dataType: "json",
        data: { 'tid': tid },
        success: (json) => {
            if (json.errno == 0) {
                toastr.success(json.msg);
                if (json.public == "0")
                    $('a:contains("' + trans('skinlib.setAsPrivate') + '")').html(trans('skinlib.setAsPublic'));
                else
                    $('a:contains("' + trans('skinlib.setAsPublic') + '")').html(trans('skinlib.setAsPrivate'));
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function deleteTexture(tid) {
    swal({
        text: trans('skinlib.deleteNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: url("skinlib/delete"),
            dataType: "json",
            data: { 'tid': tid },
            success: (json) => {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(() => window.location = url('skinlib') );
                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    });
}
