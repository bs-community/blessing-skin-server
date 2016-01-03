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
    docCookies.removeItem("uname");
    docCookies.removeItem("token");
    window.location = "./index.php";
});

$("#upload").click(function(){
    var skinFile = $("#skininput").get(0).files[0];
    var capeFile = $("#capeinput").get(0).files[0];
    
    var formData = new FormData();
    if (skinFile) {
        formData.append('skinFile', skinFile);
    }
    if (capeFile) {
        formData.append('capeFile', capeFile);
    }
    
    if (skinFile || capeFile) {
        $.ajax({
            type: 'POST',
            url: './upload.php',
            contentType: false,
            dataType: "json", 
            data: formData,
            processData: false,
            beforeSend: function() { 
        					showMsg("alert-info", "Uploading..."); 
        				},
    		success: function(json) {
    		    if (json[0].success == 1 && json[1].success == 1) {
    		        showMsg("alert-success", "Successfully uploaded."); 
    		    }
    		    if (json[0].success != 1) {
    		        showMsg("alert-danger", "Error when uploading skin:\n"+json[0].msg); 
    		    }
    		    if (json[1].success != 1) {
    		        showMsg("alert-danger", "Error when uploading cape:\n"+json[1].msg);
    		    }
    		}
        });
    } else {
        showMsg("alert-warning", "No input file selected");
    }
    
});


    
