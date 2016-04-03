/*
* @Author: printempw
* @Date:   2016-02-04 16:48:42
* @Last Modified by:   printempw
* @Last Modified time: 2016-04-03 08:23:49
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
            '<button id="upload" class="btn btn-primary fw">上传</button>',
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

function deleteTexture(uname) {
    var ply = new Ply({
        el: '<h2>选择要删除的 '+uname+' 的当前材质:</h2>'+
            '<label><input id="steve" type="checkbox" checked="">Steve 模型</label>'+
            '<label><input id="alex" type="checkbox" checked="">Alex 模型</label>'+
            '<label><input id="cape" type="checkbox" checked="">披风</label>'+
            '<label style="margin: 6px 0 12px;"><input id="all" type="checkbox" checked="">全部</label>'+
            '<button id="confirm" class="btn btn-primary fw">确定</button>',
        effect: "fade",
        onaction: function(ui) {
            if (ui.state == true) {
                var steve   = $('#steve').prop('checked');
                var alex    = $('#alex').prop('checked');
                var cape    = $('#cape').prop('checked');
                if ($('#all').prop('checked')) {
                    steve = alex = cape = true;
                }
                $.ajax({
                    type: "POST",
                    url: "admin_ajax.php?action=deleteTexture&uname="+uname,
                    dataType: "json",
                    data: {
                        "steve" : steve,
                        "alex"  : alex,
                        "cape"  : cape
                    },
                    success: function(json) {
                        if (json.errno == 0) {
                            showAlert(json.msg);
                            // remove DOM
                            if (steve) $('[src="../skin/' + uname + '-steve.png"]').remove();
                            if (alex)  $('[src="../skin/' + uname + '-alex.png"]').remove();
                            if (cape)  $('[src="../cape/' + uname + '.png"]').remove();
                        } else {
                            showAlert(json.msg);
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
            url: "admin_ajax.php?action=deleteAccount&uname="+uname,
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
