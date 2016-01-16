// Auto login
$(document).ready(function(){
    if (docCookies.hasItem("uname") && docCookies.hasItem("token") && $("#login-reg").html() == 'Register') {
        checkToken(docCookies.getItem("token"),function(json) {
            if (json.success == 1) {
                showMsg("alert-success", json.msg);
                window.location = "./user.php";
            } else {
                showMsg("alert-danger", json.msg);
            }
        });
    }
});

$('body').css('height', document.documentElement.clientHeight);

function showMsg(type, msg) {
    $("#msg").removeClass().addClass("alert").addClass(type).html(msg);
}

$("body").on("click", "#login-reg", function(){
    if ($("#login-reg").html() == 'Register') {
        $(".login-container").fadeOut(500);
        window.setTimeout("$('#login-reg').html('Login');changeForm(1)", 500);
        $(".login-container").fadeIn(500);
    } else {
        $(".login-container").fadeOut(500);
        window.setTimeout("$('#login-reg').html('Register');changeForm(0)", 500);
        $(".login-container").fadeIn(500);
    }
});

function changeForm(code){
    $("#msg").addClass("hide");
    if (code == 1) {
        $(".login-title").html('Register');
        $("#confirm-passwd").show();
        $(".login-group").html('<button id="register" type="button" class="btn btn-default">Register</button>');
        window.history.pushState(null, null, "./index.php?action=register");
        document.title = "Register - Blessing Skin Server 0.1";
    } else {
        $(".login-title").html('Login');
        $("#confirm-passwd").hide();
        $(".login-group").html('<div class="checkbox-wrapper"><input id="keep" type="checkbox" class="checkbox"><label for="keep" class="checkbox-label"></label><span>   Remember me</span></div><button id="login" type="button" class="btn btn-default">Log in</button>');
        window.history.pushState(null, null, "./index.php?action=login");
        document.title = "Login - Blessing Skin Server 0.1";
    }
}
