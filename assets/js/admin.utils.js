/*
* @Author: prpr
* @Date:   2016-02-04 16:48:42
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-04 18:02:38
*/

'use strict';

function showUpload(uname, type) {
    var ply = new Ply({
        el: '<h2>Upload new '+type+':</h2><input type="file" id="file" accept="image/png"><button id="upload" class="pure-button pure-button-primary fw">Upload</button>',
        effect: "fade",
        onaction: function(){ upload(uname, type, $('#file').get(0).files[0]); },
    });
    ply.open();
}

function upload(uname, type, file){
    var form_data = new FormData();
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
                    showAlert("Successfully uploaded.");
                    $('#'+uname+'_'+type).attr('src', 'http://skin.fuck.io/'+type+'/'+uname+'.png?t='+Math.random());
                } else {
                    showAlert("Error when uploading cape:\n" + json.msg);
                }
            }
        });
    }
}

function showAlert(msg) {
    Ply.dialog("alert", msg);
}

function showChange(uname) {
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

function showDelete(uname) {
    Ply.dialog("prompt", {
        title: "Are you sure to delete "+uname+"?",
    }).done(function(ui){
        $.ajax({
            type: "POST",
            url: "admin_ajax.php?action=delete&uname="+uname,
            dataType: "json",
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

function showModel(uname) {
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
                        showAlert(json.msg);
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
