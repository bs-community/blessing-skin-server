/*
* @Author: prpr
* @Date:   2016-01-21 13:56:40
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-05 21:20:30
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
            var fr = new FileReader();
            fr.onload = function (e) {
                var img = new Image();
                img.onload = function () {
                    if (type == "skin") {
                        MSP.changeSkin(img.src);
                    } else {
                        MSP.changeCape(img.src);
                    }
                };
                img.onerror = function () {
                    showMsg("alert-danger", "错误：这张图片编码不对哦");
                };
                img.src = this.result;
            };
          fr.readAsDataURL(file);
        } else {
            showMsg("alert-danger", "错误：皮肤文件必须为 PNG 格式");
        }
    }
};

function init3dCanvas() {
    if ($(window).width() < 800) {
        var canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    } else {
        var canvas = MSP.get3dSkinCanvas(400, 400);
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    }
}
$(document).ready(init3dCanvas);
// Auto resize canvas to fit responsive design
$(window).resize(init3dCanvas);

// Change 3D preview status
$("[title='Movements']").click(function(){
    MSP.setStatus("movements", !MSP.getStatus("movements"));
});
$("[title='Running']").click(function(){
    MSP.setStatus("running", !MSP.getStatus("running"));
});
$("[title='Rotation']").click(function(){
    MSP.setStatus("rotation", !MSP.getStatus("rotation"));
});

function show2dPreview() {
    $('#canvas3d').remove();
    $('.operations').hide();
    $("#skinpreview").html($('<p>Steve 模型的皮肤：</p>').append($('<img />').addClass('skin2d').attr('src', '../skin/'+docCookies.getItem('uname')+'-steve.png?v='+Math.random())));
    $("#skinpreview").append($('<p>Alex 模型的皮肤：</p>').append($('<img />').addClass('skin2d').attr('src', '../skin/'+docCookies.getItem('uname')+'-alex.png?v='+Math.random())));
    $("#skinpreview").append($('<p>披风：</p>').append($('<img />').addClass('skin2d').attr('src', '../cape/'+docCookies.getItem('uname')+'.png?v='+Math.random())));
    $('#preview').html('3D Preview').attr('href', 'javascript:show3dPreview();');
}

function show3dPreview() {
    $('#skinpreview').html('');
    $('.operations').show();
    init3dCanvas()
    $('#preview').html('2D Preview').attr('href', 'javascript:show2dPreview();');
}

$("#upload").click(function(){
    var model = $('#model-steve').prop('checked') ? "steve" : "alex";
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

