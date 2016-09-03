/*
 * @Author: printempw
 * @Date:   2016-07-19 10:46:38
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-09-03 21:48:17
 */

'use strict';

$(document).ready(function() {
    swal.setDefaults({
        confirmButtonText: '确定',
        cancelButtonText: '取消'
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
    $.getJSON('../skinlib/info/'+tid, function(json) {
        swal({
            title: '给你的皮肤起个名字吧~',
            inputValue: json.name,
            input: 'text',
            showCancelButton: true,
            inputValidator: function(value) {
                return new Promise(function(resolve, reject) {
                    if (value) {
                        resolve();
                    } else {
                        reject('你还没有填写名称哦');
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
        url: "../user/closet/add",
        dataType: "json",
        data: { 'tid': tid, 'name': name },
        success: function(json) {
            if (json.errno == 0) {
                swal({
                    type: 'success',
                    html: json.msg
                });

                $('.modal').modal('hide');
                $('a[tid='+tid+']').attr('href', 'javascript:removeFromCloset('+tid+');').attr('title', '从衣柜中移除').addClass('liked');
                $('#'+tid).attr('href', 'javascript:removeFromCloset('+tid+');').html('从衣柜中移除');
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
        text: '确定要从衣柜中移除此材质吗？',
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#3085d6',
        confirmButtonColor: '#d33'
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "../user/closet/remove",
            dataType: "json",
            data: { 'tid' : tid },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });

                    $('a[tid='+tid+']').attr('href', 'javascript:addToCloset('+tid+');').attr('title', '添加至衣柜').removeClass('liked');
                    $('#'+tid).attr('href', 'javascript:addToCloset('+tid+');').html('添加至衣柜');
                    $('#likes').html(parseInt($('#likes').html()) - 1);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });

}

function init3dCanvas() {
    if ($(window).width() < 800) {
        var canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    } else {
        var canvas = MSP.get3dSkinCanvas(350, 350);
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    }
}

// Change 3D preview status
$('.fa-pause').click(function(){
    MSP.setStatus('movements', !MSP.getStatus('movements'));

    if ($(this).hasClass('fa-pause'))
        $(this).removeClass('fa-pause').addClass('fa-play');
    else
        $(this).removeClass('fa-play').addClass('fa-pause');
    // stop rotation when pause
    MSP.setStatus('rotation', !MSP.getStatus('rotation'));
});
$('.fa-forward').click(function(){
    MSP.setStatus('running', !MSP.getStatus('running'));
});
$('.fa-repeat').click(function(){
    MSP.setStatus('rotation', !MSP.getStatus('rotation'));
});

$('body').on('change', '#file', function() {
    var files = $('#file').prop('files');
    var type = ($('#type').val() == "cape") ? "cape" : "skin";
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
                    toastr.warning('错误：这张图片编码不对哦');
                };
                img.src = this.result;
            };
            reader.readAsDataURL(file);
        } else {
            toastr.warning('错误：皮肤文件必须为 PNG 格式');
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
        toastr.info('请选择材质的类型'); return;
    }

    // quick fix for browsers which don't support FormData.get()
    if ($('#file').prop('files')[0] == 'undefined') {
        toastr.info('你还没有上传任何文件哦');
        $('#file').focus();
    } else if ($('#name').val() == "") {
        toastr.info('给你的材质起个名字吧');
        $('#name').focus();
    } else if ($('#file').prop('files')[0].type !== "image/png") {
        toastr.warning('请选择 PNG 格式的图片');
        $('#file').focus();
    } else {
        $.ajax({
            type: "POST",
            url: "../skinlib/upload",
            contentType: false,
            dataType: "json",
            data: form_data,
            processData: false,
            beforeSend: function() {
                $('#upload-button').html('<i class="fa fa-spinner fa-spin"></i> 上传中').prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(function() {
                        toastr.info('正在跳转...');
                        window.setTimeout('window.location = "./show?tid='+json.tid+'"', 1000);
                    });
                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    }).then(function() {
                        $('#upload-button').html('确认上传').prop('disabled', '');
                    });
                }
            },
            error: function(json) {
                $('#upload-button').html('确认上传').prop('disabled', '');
                showAjaxError(json);
            }
        });
    }
    return false;
}

function changeTextureName(tid) {
    swal({
        text: '请输入新的材质名称：',
        input: 'text',
        showCancelButton: true,
        inputValidator: function(value) {
            return new Promise(function(resolve, reject) {
                if (value) {
                    resolve();
                } else {
                    reject('你还没有填写名称哦');
                }
            });
        }
    }).then(function(new_name) {
        $.ajax({
            type: "POST",
            url: "../skinlib/rename",
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
    var object = $(this);
    swal({
        text: '要将此材质设置为公开吗？',
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        changePrivacy(object.attr('tid'));
        object.remove();
    });
});

function changePrivacy(tid) {
    $.ajax({
        type: "POST",
        url: "../skinlib/privacy/" + tid,
        dataType: "json",
        success: function(json) {
            if (json.errno == 0) {
                toastr.success(json.msg);
                if (json.public == "0")
                    $('a:contains("设为隐私")').html('设为公开');
                else
                    $('a:contains("设为公开")').html('设为隐私');
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function deleteTexture(tid) {
    swal({
        text: '真的要删除此材质吗？积分将会被返还',
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "../skinlib/delete",
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
