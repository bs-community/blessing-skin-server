/*
* @Author: prpr
* @Date:   2016-07-19 10:46:38
* @Last Modified by:   printempw
* @Last Modified time: 2016-07-22 10:25:54
*/

'use strict';

function addToCloset(tid) {
    var dom = '<div class="form-group">'+
                    '<label for="new-name">给你的皮肤起个名字吧~</label>'+
                    '<input id="new-name" class="form-control" type="text" placeholder="" />'+
                '</div><br />';
    showModal(dom, '收藏新皮肤', 'default', 'ajaxAddToCloset('+tid+')');
    return;
}

function ajaxAddToCloset(tid) {
    var name = $('#new-name').val();

    if (name == "") {
        toastr.info('你还没有填写名称哦');
        $('#name').focus(); return;
    }

    $.ajax({
        type: "POST",
        url: "../user/closet/add",
        dataType: "json",
        data: { 'tid': tid, 'name': name },
        success: function(json) {
            if (json.errno == 0) {
                toastr.success(json.msg);
                $('.modal').modal('hide');
                $('a[tid='+tid+']').attr('href', 'javascript:removeFromCloset('+tid+');').attr('title', '从衣柜中移除').addClass('liked');
                $('#'+tid).attr('href', 'javascript:removeFromCloset('+tid+');').html('从衣柜中移除');
                $('#likes').html(parseInt($('#likes').html()) + 1);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}

function removeFromCloset(tid) {
    $.ajax({
        type: "POST",
        url: "../user/closet/remove",
        dataType: "json",
        data: { 'tid' : tid },
        success: function(json) {
            if (json.errno == 0) {
                toastr.success(json.msg);
                $('a[tid='+tid+']').attr('href', 'javascript:addToCloset('+tid+');').attr('title', '添加至衣柜').removeClass('liked');
                $('#'+tid).attr('href', 'javascript:addToCloset('+tid+');').html('添加至衣柜');
                $('#likes').html(parseInt($('#likes').html()) - 1);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
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
}).on('change', '#type', function() {
    MSP.clear();
    var files = $('#file').prop('files');
    var type = ($('#type').val() == "cape") ? "cape" : "skin";
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
    form_data.append('name', $('#name').val())
    form_data.append('type', $('#type').val())
    form_data.append('file', $('#file').prop('files')[0])
    form_data.append('public', !$('#private').prop('checked'))

    if (form_data.get('file') == 'undefined') {
        toastr.info('你还没有上传任何文件哦');
        $('#file').focus();
    } else if (form_data.get('name') == "") {
        toastr.info('给你的材质起个名字吧');
        $('#name').focus();
    } else if (form_data.get('type') == "") {
        toastr.info('请选择材质的类型');
        $('#type').focus();
    } else if (form_data.get('file.type') === "image/png") {
        toastr.warning('请选择 PNG 格式的图片');
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
                $('#upload-button').html('<i class="fa fa-spinner fa-spin"></i> 上传中').prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    toastr.success(json.msg);
                    toastr.info('正在跳转...');
                    window.setTimeout('window.location = "./show?tid='+json.tid+'"', 2500);
                } else {
                    $('#upload-button').html('确认上传').prop('disabled', '');
                    toastr.warning(json.msg);
                }
            },
            error: function(json) {
                $('#upload-button').html('确认上传').prop('disabled', '');
                showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
            }
        });
    }
    return false;
}

function changePrivacy(tid) {
    $.ajax({
        type: "POST",
        url: "./privacy/" + tid,
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
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}

function deleteTexture(tid) {
    if (!window.confirm('真的要删除此材质吗？积分将会被返还')) return;

    $.ajax({
        type: "POST",
        url: "./delete",
        dataType: "json",
        data: { 'tid': tid },
        success: function(json) {
            if (json.errno == 0) {
                toastr.success(json.msg);
                window.setTimeout('window.location = "./"', 1000);
            } else {
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
        }
    });
}
