/*
* @Author: prpr
* @Date:   2016-02-04 16:48:42
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-04 18:27:44
*/

'use strict';

function uploadTexture(uname, type) {
    var ply = new Ply({
        el: '<h2>Upload new '+type+':</h2>'+
            '<input type="file" id="file" accept="image/png">'+
            '<button id="upload" class="pure-button pure-button-primary fw">Upload</button>',
        effect: "fade",
        onaction: function(){
            var form_data = new FormData();
            var file = $('#file').get(0).files[0];
            if (file) {
                form_data.append('file', file);
                $.ajax({
                    type: 'POST',
                    contentType: false,
                    url: 'admin_ajax.php?action=upload&type='+type+'&uname='+uname,
                    dataType: "json",
                    data: form_data,
                    processData: false,
                    success: function(json) {
                        if (json.errno == 0) {
                            showAlert("Successfully uploaded.", function(){
                                location.reload();
                            });
                        } else {
                            showAlert("Error when uploading cape:\n" + json.msg);
                        }
                    }
                });
            }
        },
    });
    ply.open();
}


function changePasswd(uname) {
    Ply.dialog("prompt", {
        title: "Type in "+uname+"'s new password",
        form: { passwd: "New Password" }
    }).done(function(ui){
        var passwd = ui.data.passwd;
        $.ajax({
            type: "POST",
            url: "admin_ajax.php?action=change&uname="+uname,
            dataType: "json",
            data: { "passwd": passwd },
            success: function(json) {
                if (json.errno == 0) {
                    showAlert(json.msg);
                } else {
                    showAlert(json.msg);
                }
            }
        });
    });
}

function deleteAccount(uname) {
    Ply.dialog("prompt", {
        title: "Are you sure to delete "+uname+"?",
    }).done(function(ui){
        $.ajax({
            type: "POST",
            url: "admin_ajax.php?action=delete&uname="+uname,
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

function changeModel(uname) {
    Ply.dialog("prompt", {
        title: "Change "+uname+"'s model prefrence:",
        form: { text: "Type in `slim` or `default`" }
    }).done(function(ui){
        var model = ui.data.text;
        if (model == 'slim'| model == 'default') {
            $.ajax({
                type: "POST",
                url: "admin_ajax.php?action=model&uname="+uname,
                data: { "model": ui.data.text },
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
        } else {
            showAlert('Only `slim` or `default` is valid.');
        }
    });
}
