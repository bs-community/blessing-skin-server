/*
* @Author: prpr
* @Date:   2016-02-04 16:48:42
* @Last Modified by:   prpr
* @Last Modified time: 2016-02-05 22:01:44
*/

'use strict';

function uploadSkin(uname) {
    Ply.dialog("confirm", {
        text: "更改该用户对应哪个模型的皮肤？",
        ok: "Steve",
        cancel: "Alex"
    }).done(function(){
        uploadTexture(uname, 'steve');
    }).fail(function(){
        uploadTexture(uname, 'alex');
    });
}

function uploadTexture(uname, type) {
    var ply = new Ply({
        el: '<h2>为该用户上传新的 '+type+':</h2>'+
            '<input type="file" id="file" accept="image/png">'+
            '<button id="upload" class="pure-button pure-button-primary fw">上传</button>',
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
                            showAlert("上传成功。", function(){
                                location.reload();
                            });
                        } else {
                            showAlert("上传材质的时候出错啦：\n" + json.msg);
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
        title: "修改 "+uname+" 的登录密码：",
        form: { passwd: "新的密码" }
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
        title: "确定要删除 "+uname+"？此操作不可恢复。",
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
        title: "修改 "+uname+" 的优先皮肤模型：",
        form: { text: "输入 `slim` 或者 `default`" }
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
            showAlert('只能输入 `slim` 或者 `default` 哦');
        }
    });
}
