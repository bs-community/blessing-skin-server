/*
* @Author: prpr
* @Date:   2016-01-21 13:56:40
* @Last Modified by:   prpr
* @Last Modified time: 2016-01-21 22:58:32
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

function showMsg(type, msg) {
    $("#msg").removeClass().addClass("alert").addClass(type).html(msg);
}

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
                    alert("Error: Not an image or unknown file format");
                };
                img.src = this.result;
            };
            fr.readAsDataURL(file);
        } else {
            alert("Error: This is not a PNG image!");
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
            var path = "/" + document.URL.split("/").slice(-3)[0];
            docCookies.removeItem("uname", path);
            docCookies.removeItem("token", path);
            showMsg('alert-success', json.msg);
            window.setTimeout(function(){
                window.location = "../index.php?msg=Successfully logged out.";
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
                if (json[0].errno == 0 && json[1].errno == 0) {
                    showMsg("alert-success", "Successfully uploaded.");
                }
                if (json[0].errno != 0) {
                    showMsg("alert-danger", "Error when uploading skin:\n"+json[0].msg);
                }
                if (json[1].errno != 0) {
                    showMsg("alert-danger", "Error when uploading cape:\n"+json[1].msg);
                }
            }
        });
    } else {
        showMsg("alert-warning", "No input file selected");
    }

});



