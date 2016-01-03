function checkToken(token, handler) {
    $.ajax({ 
		type: "POST", 
		url: "check.php?action=token", 
		dataType: "json", 
		data: {"token":token}, 
		success: function(json) { 
            		handler(json);
            	}
	});
}

function checkForm(type, uname, passwd, passwd2) {
    if (type == "login") {
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
    } else if (type == "register") {
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
    }
}
// Login Button Click Event
$("body").on("click", "#login", function(){
	var uname = $("#uname").val(); 
	var passwd = $("#passwd").val(); 
	if (checkForm("login", uname, passwd)) {
    	$.ajax({ 
    		type: "POST", 
    		url: "check.php?action=login", 
    		dataType: "json", 
    		data: {"uname":uname,"passwd":passwd}, 
    		beforeSend: function() { 
        					showMsg("alert-info", "Logging in..."); 
        				},
    		success: function(json) { 
                		if (json.success == 1) { 
                		    docCookies.setItem("uname", uname);
                		    docCookies.setItem("token", json.token);
    						if ($("#keep").prop("checked")) {
    						    docCookies.setItem("uname", uname, 604800);
    							// 设置长效 token （7天）
    							docCookies.setItem("token", json.token, 604800);
    						}
    						showMsg("alert-success", "Logging succeed!");
    						window.setTimeout("window.location = './user.php'", 1000);
    					} else { 
    						showMsg("alert-danger", json.msg);
    					} 
                	}
    	});
    }
});

// Register Button Click Event
$("body").on("click", "#register", function(){
	var uname = $("#uname").val(); 
	var passwd = $("#passwd").val(); 
	if (checkForm("register", uname, passwd, $("#cpasswd").val())) {
	    
	$.ajax({ 
		type: "POST", 
		url: "check.php?action=register", 
		dataType: "json", 
		data: {"uname":uname,"passwd":passwd}, 
		beforeSend: function() { 
    					showMsg("alert-info", "Registering..."); 
    				},
		success: function(json) { 
            		if (json.success == 1) { 
						showMsg("alert-success", json.msg);
						window.setTimeout("window.location = './index.php?action=login&msg=Successfully Registered, please log in.'", 1000);
					} else { 
						showMsg("alert-danger", json.msg);
					} 
            	}
	});

}});


