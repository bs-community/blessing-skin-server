/*
* @Author: printempw
* @Date:   2016-01-21 13:56:40
* @Last Modified by:   printempw
* @Last Modified time: 2016-03-27 09:47:10
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
                    showCallout("alert-danger", "错误：这张图片编码不对哦");
                };
                img.src = this.result;
            };
            reader.readAsDataURL(file);
        } else {
            showCallout("alert-danger", "错误：皮肤文件必须为 PNG 格式");
        }
    }
};

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

function change2dTexture(type, file) {
    $('#'+type).attr('src', file);
}

function onWindowResize() {
    if (preview_type == "3d") {
        camera.aspect = (window.innerWidth - sidebarWidth) / window.innerHeight;
        camera.updateProjectionMatrix();

        var canvas3d = document.getElementById('canvas3d');
        canvas3d.width = 600;
        canvas3d.height = 350;

        canvas3d.setSize(container.clientWidth, container.clientWidth/12*7);
    } else {
        show2dPreview();
    }
}
