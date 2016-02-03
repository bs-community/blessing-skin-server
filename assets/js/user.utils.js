/*
* @Author: prpr
* @Date:   2016-01-21 13:56:40
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-03 10:29:24
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

var canvas = MSP.get3dSkinCanvas(500, 500);
$("#skinpreview").append($(canvas).prop("id", "canvas3d"));

$("[title='Movements']").click(function(){
	if (MSP.getStatus("movements")) {
		MSP.setStatus("movements", false);
	} else {
		MSP.setStatus("movements", true);
	}
});

$("[title='Running']").click(function(){
	if (MSP.getStatus("running")) {
		MSP.setStatus("running", false);
	} else {
		MSP.setStatus("running", true);
	}
});

$("[title='Rotation']").click(function(){
	if (MSP.getStatus("rotation")) {
		MSP.setStatus("rotation", false);
	} else {
		MSP.setStatus("rotation", true);
	}
});

$("#logout").click(function(){
	$.ajax({
		type: "POST",
		url: "../ajax.php?action=logout",
		dataType: "json",
		data: {"uname": docCookies.getItem('uname')},
		success: function(json) {
			docCookies.removeItem("uname", "/");
			docCookies.removeItem("token", "/");
			showAlert(json.msg + " Successfully logged out.");
			window.setTimeout(function(){
				window.location = "../index.php";
			}, 1000);
		}
	});
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
				if (json['skin'].errno == 0 && json['cape'].errno == 0) {
					showMsg("alert-success", "Successfully uploaded.");
				}
				if (json['skin'].errno != 0) {
					showMsg("alert-danger", "Error when uploading skin:\n"+json['skin'].msg);
				}
				if (json['cape'].errno != 0) {
					showMsg("alert-danger", "Error when uploading cape:\n"+json['cape'].msg);
				}
			}
		});
	} else {
		showMsg("alert-warning", "No input file selected");
	}

});



