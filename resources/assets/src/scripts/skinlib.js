/*
 * @Author: printempw
 * @Date:   2016-07-19 10:46:38
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-20 20:57:52
 */

'use strict';

var base_url = location.pathname.endsWith('skinlib') ? "." : "..";

$(document).ready(function() {
    swal.setDefaults({
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    });
});

$('#page-select').on('change', function() {
    // if has query strings
    if (getQueryString('filter') != "" || getQueryString('sort') != "") {
        if (getQueryString('page') == "")
            window.location = location.href + "&page=" + $(this).val();
        else
            window.location = "?filter="+getQueryString('filter')+"&sort="+getQueryString('sort')+"&page="+$(this).val();
    } else {
        window.location = "?page=" + $(this).val();
    }

});

$('#private').on('ifToggled', function() {
    $(this).prop('checked') ? $('#msg').show() : $('#msg').hide();
});

$('#type-skin').on('ifToggled', function() {
    $(this).prop('checked') ? $('#skin-type').show() : $('#skin-type').hide();
});

function addToCloset(tid) {
    $.getJSON(base_url + '/skinlib/info/'+tid, function(json) {
        swal({
            title: trans('skinlib.setItemName'),
            inputValue: json.name,
            input: 'text',
            showCancelButton: true,
            inputValidator: function(value) {
                return new Promise(function(resolve, reject) {
                    if (value) {
                        resolve();
                    } else {
                        reject(trans('skinlib.emptyItemName'));
                    }
                });
            }
        }).then(function(result) {
            ajaxAddToCloset(tid, result);
        });
    });
}

function ajaxAddToCloset(tid, name) {
    // remove interference of modal which is hide
    $('.modal').each(function() {
        if ($(this).css('display') == "none")
            $(this).remove();
    });

    $.ajax({
        type: "POST",
        url: base_url + "/user/closet/add",
        dataType: "json",
        data: { 'tid': tid, 'name': name },
        success: function(json) {
            if (json.errno == 0) {
                swal({
                    type: 'success',
                    html: json.msg
                });

                $('.modal').modal('hide');
                $('a[tid='+tid+']').attr('href', 'javascript:removeFromCloset('+tid+');').attr('title', trans('skinlib.removeFromCloset')).addClass('liked');
                $('#'+tid).attr('href', 'javascript:removeFromCloset('+tid+');').html(trans('skinlib.removeFromCloset'));
                $('#likes').html(parseInt($('#likes').html()) + 1);
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
    }).then(function() {
        $.ajax({
            type: "POST",
            url: base_url + "/user/closet/remove",
            dataType: "json",
            data: { 'tid' : tid },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });

                    $('a[tid='+tid+']').attr('href', 'javascript:addToCloset('+tid+');').attr('title', trans('skinlib.addToCloset')).removeClass('liked');
                    $('#'+tid).attr('href', 'javascript:addToCloset('+tid+');').html(trans('skinlib.addToCloset'));
                    $('#likes').html(parseInt($('#likes').html()) - 1);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });

}

$('body').on('change', '#file', function() {
    var files = $('#file').prop('files');
    var type = $('#type-cape').prop('checked') ? "cape" : "skin";
    handleFiles(files, type);
}).on('ifToggled', '#type-cape', function() {
    MSP.clear();
    var files = $('#file').prop('files');
    var type = $('#type-cape').prop('checked') ? "cape" : "skin";
    handleFiles(files, type);
});

// Real-time preview
function handleFiles(files, type) {
    if (files.length > 0) {
        var file = files[0];
        if (file.type === "image/png") {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    (type == "skin") ? MSP.changeSkin(img.src) : MSP.changeCape(img.src);
                    if ($('#name').val() == "")
                        $('#name').val(file.name.split('.png')[0])
                };
                img.onerror = function() {
                    toastr.warning(trans('skinlib.fileExtError'));
                };
                img.src = this.result;
            };
            reader.readAsDataURL(file);
        } else {
            toastr.warning(trans('skinlib.formatError'));
        }
    }
};

function upload() {
    var form_data = new FormData();
    form_data.append('name', $('#name').val());
    form_data.append('file', $('#file').prop('files')[0]);
    form_data.append('public', !$('#private').prop('checked'));

    if ($('#type-skin').prop('checked')) {
        form_data.append('type', $('#skin-type').val());
    } else if ($('#type-cape').prop('checked')) {
        form_data.append('type', 'cape');
    } else {
        toastr.info(trans('skinlib.emptyTextureType')); return;
    }

    // quick fix for browsers which don't support FormData.get()
    if ($('#file').prop('files')[0] === undefined) {
        toastr.info(trans('skinlib.emptyUploadFile'));
        $('#file').focus();
    } else if ($('#name').val() == "") {
        toastr.info(trans('skinlib.emptyTextureName'));
        $('#name').focus();
    } else if ($('#file').prop('files')[0].type !== "image/png") {
        toastr.warning(trans('skinlib.fileExtError'));
        $('#file').focus();
    } else {
        $.ajax({
            type: "POST",
            url: "./upload",
            contentType: false,
            dataType: "json",
            data: form_data,
            processData: false,
            beforeSend: function() {
                $('#upload-button').html('<i class="fa fa-spinner fa-spin"></i> ' + trans('skinlib.uploading')).prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    var redirect = function() {
                        toastr.info(trans('skinlib.redirecting'));
                        window.setTimeout('window.location = "./show?tid='+json.tid+'"', 1000);
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
                    }).then(function() {
                        $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
                    });
                }
            },
            error: function(json) {
                $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
                showAjaxError(json);
            }
        });
    }
    return false;
}

function changeTextureName(tid) {
    swal({
        text: trans('skinlib.setNewTextureName'),
        input: 'text',
        showCancelButton: true,
        inputValidator: function(value) {
            return new Promise(function(resolve, reject) {
                if (value) {
                    resolve();
                } else {
                    reject(trans('skinlib.emptyNewTextureName'));
                }
            });
        }
    }).then(function(new_name) {
        $.ajax({
            type: "POST",
            url: "./rename",
            dataType: "json",
            data: { 'tid': tid, 'new_name': new_name },
            success: function(json) {
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

$('.private-label').click(function() {
    var self = $(this);
    swal({
        text: trans('skinlib.setPublicNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        changePrivacy(self.attr('tid'));
        self.remove();
    });
});

function changePrivacy(tid) {
    $.ajax({
        type: "POST",
        url: "./privacy/" + tid,
        dataType: "json",
        success: function(json) {
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
            url: "./delete",
            dataType: "json",
            data: { 'tid': tid },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(function() {
                        window.location = "./";
                    });
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
