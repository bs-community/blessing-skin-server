/*
* @Author: prpr
* @Date:   2016-01-21 13:56:40
* @Last Modified by:   printempw
* @Last Modified time: 2016-02-28 14:19:37
*/

'use strict';

$('body').on('change', '#skininput', function(){
    var files = $('#skininput').prop('files');
    handleFiles(files, 'skin');
});

$('body').on('change', '#capeinput', function(){
    var files = $('#capeinput').prop('files');
    handleFiles(files, 'cape');
});

// Real-time preview
function handleFiles(files, type) {
    if(files.length > 0) {
        var file = files[0];
        if(file.type === "image/png") {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = new Image();
                img.onload = function () {
                    if (type == "skin") {
                        MSP.changeSkin(img.src);
                        var model = $('#model-alex').prop('checked') ? "alex" : "steve";
                        change2dTexture(model, img.src);
                    } else {
                        MSP.changeCape(img.src);
                        change2dTexture('cape', img.src);
                    }
                };
                img.onerror = function () {
                    showMsg("alert-danger", "错误：这张图片编码不对哦");
                };
                img.src = this.result;
            };
            reader.readAsDataURL(file);
        } else {
            showMsg("alert-danger", "错误：皮肤文件必须为 PNG 格式");
        }
    }
};

function init3dCanvas() {
    MSP.init(document.getElementById('skinpreview'));
    if ($(window).width() < 1000) {
        MSP.setSize($('#skinpreview').width(), $('#skinpreview').width())
    } else {
        MSP.setSize(600, 400)
    }
}
$(document).ready(init3dCanvas);
// Auto resize canvas to fit responsive design
$(window).resize(init3dCanvas);

// Change 3D preview status
var run = false, stop = false;
$("[title='Movements']").click(function(){
    stop = !stop;
    MSP.stopPlay(stop);
});
$("[title='Running']").click(function(){
    run = !run;
    MSP.playQuickly(run);
});

$('#model-alex').on('change', function() {
    if ($('#model-alex').prop('checked') == true) {
        showMsg("alert-info", "提示：3D 预览暂时不支持 Alex 模型，预览可能会出现渲染错误。"+
                                 "不要在意直接上传即可，<b>游戏中</b>显示是没有问题的。");
    }
});

$('#model-steve').on('change', function() {
    if ($('#model-steve').prop('checked') == true) {
        showMsg("hide");
    }
});

function show2dPreview() {
    $('#canvas3d').remove();
    $('.operations').hide();
    $('#preview-2d').show();
    $('#preview').html('3D 皮肤预览').attr('href', 'javascript:show3dPreview();');
}

function show3dPreview() {
    $('#preview-2d').hide();
    $('.operations').show();
    init3dCanvas()
    $('#preview').html('2D 皮肤预览').attr('href', 'javascript:show2dPreview();');
}

$("#upload").click(function(){
    var model = $('#model-alex').prop('checked') ? "alex" : "steve";
    var skin_file = $('#skininput').get(0).files[0];
    var cape_file = $('#capeinput').get(0).files[0];
    var form_data = new FormData();
    if (skin_file) form_data.append('skin_file', skin_file);
    if (cape_file) form_data.append('cape_file', cape_file);
    form_data.append('uname', docCookies.getItem('uname'));
    // Ajax file upload
    if (skin_file || cape_file) {
        $.ajax({
            type: 'POST',
            url: '../ajax.php?action=upload&model='+model,
            contentType: false,
            dataType: "json",
            data: form_data,
            processData: false,
            beforeSend: function() {
                            showMsg('alert-info', '正在上传。。');
                        },
            success: function(json) {
                console.log(json);
                if (json.skin.errno == 0 && json.cape.errno == 0) {
                    showMsg('alert-success', '上传成功！');
                }
                if (json.skin.errno != 0) {
                    showMsg('alert-danger', '上传皮肤的时候出错了：\n'+json.skin.msg);
                }
                if (json.cape.errno != 0) {
                    showMsg('alert-danger', '上传披风的时候出错了：\n'+json.cape.msg);
                }
            }
        });
    } else {
        showMsg('alert-warning', '你还没有选择任何文件哦');
    }
});

function changeModel(uname) {
    showAlert('确定要更改优先皮肤模型吗？', function(){
        $.ajax({
            type: "POST",
            url: "../ajax.php?action=model",
            data: { "uname": docCookies.getItem('uname') },
            dataType: "json",
            success: function(json) {
                if (json.errno == 0) {
                    showAlert(json.msg, function(){
                        location.reload();
                    });
                } else {
                    showAlert(json.msg);
                }
            }
        });
    });
}

$(document).ready(function(){
    $('#preview-2d').hide();
    $('#model-steve').prop('checked', true);
});

function change2dTexture(type, file) {
    $('#'+type).attr('src', file);
}
