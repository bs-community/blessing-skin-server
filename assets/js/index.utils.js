/*
* @Author: prpr
* @Date:   2016-01-21 13:55:44
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-02 21:41:26
*/

'use strict';

$('#login').click(function(){
	$('[data-remodal-id=login-modal]').remodal().open();
})

$('#register').click(function(){
	$('[data-remodal-id=register-modal]').remodal().open();
})

// Login Button Click Event
$("body").on("click", "#login-button", function(){
	var uname = $("#uname").val();
	var passwd = $("#passwd").val();
	if (checkForm("login", uname, passwd)) {
	    	$.ajax({
	    		type: "POST",
	    		url: "ajax.php?action=login",
	    		dataType: "json",
	    		data: {"uname":uname,"passwd":passwd},
	    		beforeSend: function() {
				showMsg("alert-info", "Logging in...");
			},
	    		success: function(json) {
	        		if (json.errno == 0) {
	        		    docCookies.setItem("uname", uname, '/');
	        		    docCookies.setItem("token", json.token, '/');
					if ($("#keep").prop("checked")) {
					    docCookies.setItem("uname", uname, 604800, '/');
						// 设置长效 token （7天）
						docCookies.setItem("token", json.token, 604800, '/');
					}
					showAlert("Logging succeed!");
					window.setTimeout("window.location = './user/index.php'", 1000);
				} else {
					showAlert(json.msg);
				}
	        	}
	    	});
    }
});

// Register Button Click Event
$("body").on("click", "#register-button", function(){
	var uname = $("#reg-uname").val();
	var passwd = $("#reg-passwd").val();
	if (checkForm("register", uname, passwd, $("#reg-passwd2").val())) {
		$.ajax({
			type: "POST",
			url: "ajax.php?action=register",
			dataType: "json",
			data: {"uname":uname, "passwd":passwd},
			beforeSend: function() {
				showMsg("alert-info", "Registering...");
			},
			success: function(json) {
	        		if (json.errno == 0) {
					showAlert(json.msg + " Please log in.");
					$('[data-remodal-id=register-modal]').remodal().close();
					showMsg('hide', "");
				} else {
					showMsg('hide', "");
					showAlert(json.msg);
				}
	        	}
		});
	}
});

function checkForm(type, uname, passwd, passwd2) {
	switch(type) {
		case "login":
			if (uname === "") {
				showMsg("alert-warning", "Empty Username!");
				$("#uname").focus();
				return false;
			} else if (passwd === ""){
				showMsg("alert-warning", "Empty Password!");
				$("#passwd").focus();
				return false;
			} else {
				return true;
			}
			break;
		case "register":
			if (uname === "") {
				showMsg("alert-warning", "Empty Username!");
				$("#uname").focus();
				return false;
			} else if (passwd === ""){
				showMsg("alert-warning", "Empty Password!");
				$("#passwd").focus();
				return false;
			} else if (passwd2 === ""){
				showMsg("alert-warning", "Empty Comfirming Password!");
				$("#cpasswd").focus();
				return false;
			} else if (passwd != passwd2){
				showMsg("alert-warning", "Non-equal password comfirming!");
				$("#cpasswd").focus();
				return false;
			} else {
				return true;
			}
			break;
		default:
			return false;
	}
}
