/*
* @Author: prpr
* @Date:   2016-01-21 13:56:40
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-04 23:38:39
*/

'use strict';

$("body").on("change", "#skininput", function(){
	var files = $("#skininput").prop("files");
	handleFiles(files, "skin");
});

$("body").on("change", "#capeinput", function(){
	var files = $("#capeinput").prop("files");
	handleFiles(files, "cape");
});

var handleFiles = function (files, type) {
	if(files.length > 0) {
		var file = files[0];
		if(file.type === 'image/png') {
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
					showMsg("alert-danger", "Error: Not an image or unknown file format");
				};
				img.src = this.result;
				};
			  fr.readAsDataURL(file);
		 } else {
			showMsg("alert-danger", "Error: This is not a PNG image!");
		}
	}
};

if ($(window).width() < 600) {
	var canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
	$("#skinpreview").append($(canvas).prop("id", "canvas3d"));
} else {
	var canvas = MSP.get3dSkinCanvas(400, 400);
	$("#skinpreview").append($(canvas).prop("id", "canvas3d"));
}

$(window).resize(function(){
	if ($(document).height() <= $(window).height() || $(window).width() < 600) {
		var canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
		$("#skinpreview").append($(canvas).prop("id", "canvas3d"));
	} else {
		var canvas = MSP.get3dSkinCanvas(400, 400);
		$("#skinpreview").append($(canvas).prop("id", "canvas3d"));
	}
});

$("[title='Movements']").click(function(){
	MSP.setStatus("movements", !MSP.getStatus("movements"));
});

$("[title='Running']").click(function(){
	MSP.setStatus("running", !MSP.getStatus("running"));
});

$("[title='Rotation']").click(function(){
	MSP.setStatus("rotation", !MSP.getStatus("rotation"));
});

$("#upload").click(function(){
	var skin_file = $("#skininput").get(0).files[0];
	var cape_file = $("#capeinput").get(0).files[0];

	var form_data = new FormData();
	if (skin_file) {
		form_data.append('skin_file', skin_file);
	}
	if (cape_file) {
		form_data.append('cape_file', cape_file);
	}
	form_data.append('uname', docCookies.getItem('uname'));
	if (skin_file || cape_file) {
		$.ajax({
			type: 'POST',
			url: '../ajax.php?action=upload',
			contentType: false,
			dataType: "json",
			data: form_data,
			processData: false,
			beforeSend: function() {
							showMsg("alert-info", "Uploading...");
						},
			success: function(json) {
				console.log(json);
				if (json.skin.errno == 0 && json.cape.errno == 0) {
					showMsg("alert-success", "Successfully uploaded.");
				}
				if (json.skin.errno != 0) {
					showMsg("alert-danger", "Error when uploading skin:\n"+json.skin.msg);
				}
				if (json.cape.errno != 0) {
					showMsg("alert-danger", "Error when uploading cape:\n"+json.cape.msg);
				}
			}
		});
	} else {
		showMsg("alert-warning", "No input file selected");
	}
});



