/*
* @Author: printempw
* @Date:   2016-03-27 09:43:57
* @Last Modified by:   printempw
* @Last Modified time: 2016-04-03 11:55:42
*/

'use strict';

var preview_type = "3d";

function init3dCanvas() {
    if (preview_type == "2d") return;
    if ($(window).width() < 800) {
        var canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    } else {
        var canvas = MSP.get3dSkinCanvas(400, 400);
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    }
}
$(document).ready(function(){
    $('#preview-2d').hide();
    $('#model-steve').prop('checked', true);

    if (isMobile()) {
        show2dPreview();
    } else {
        init3dCanvas();
        MSP.setStatus("rotation", false);
    }
});

// Auto resize canvas to fit responsive design
$(window).resize(init3dCanvas);

// Change 3D preview status
$('.fa-pause').click(function(){
    MSP.setStatus("movements", !MSP.getStatus("movements"));
    if ($(this).hasClass('fa-pause'))
        $(this).removeClass('fa-pause').addClass('fa-play');
    else
        $(this).removeClass('fa-play').addClass('fa-pause');
});
$('.fa-forward').click(function(){
    MSP.setStatus("running", !MSP.getStatus("running"));
});
$('.fa-repeat').click(function(){
    MSP.setStatus("rotation", !MSP.getStatus("rotation"));
});

function show2dPreview() {
    preview_type = "2d";
    $('#canvas3d').remove();
    $('#preview-msg').remove();
    $('.operations').hide();
    $('#preview-2d').show();
    $('#preview').html('切换 3D 预览').attr('href', 'javascript:show3dPreview();');
}

function show3dPreview() {
    if (isMobile() && preview_type == "2d") {
        $("#skinpreview").append($('<div id="preview-msg" class="alert alert-info alert-dismissible fade in"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>手机上的 3D 预览可能会出现奇怪的问题（譬如空白一片），亟待解决。</div>'));
    }
    preview_type = "3d";
    init3dCanvas();
    $('#preview-2d').hide();
    $('.operations').show();
    $('#preview').html('切换 2D 预览').attr('href', 'javascript:show2dPreview();');
}
