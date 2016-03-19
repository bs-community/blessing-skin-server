/*
* @Author: printempw
* @Date:   2016-03-18 21:58:09
* @Last Modified by:   printempw
* @Last Modified time: 2016-03-19 10:03:32
*/

'use strict';

$("#upload").click(function(){
    var model = $('#model-alex').prop('checked') ? "alex" : "steve";
    var skin_file = $('#skininput').get(0).files[0];
    var cape_file = $('#capeinput').get(0).files[0];
    var form_data = new FormData();
    if (skin_file) form_data.append('skin_file', skin_file);
    if (cape_file) form_data.append('cape_file', cape_file);
    form_data.append('uname', docCookies.getItem('uname'));
    // Ajax file upload
    if (skin_file || cape_file) {
        $.ajax({
            type: 'POST',
            url: '../ajax.php?action=upload&model='+model,
            contentType: false,
            dataType: "json",
            data: form_data,
            processData: false,
            beforeSend: function() {
                            showCallout('callout-info', '正在上传。。');
                        },
            success: function(json) {
                console.log(json);
                if (json.skin.errno == 0 && json.cape.errno == 0) {
                    showCallout('callout-success', '上传成功！');
                }
                if (json.skin.errno != 0) {
                    showCallout('callout-danger', '上传皮肤的时候出错了：\n'+json.skin.msg);
                }
                if (json.cape.errno != 0) {
                    showCallout('callout-danger', '上传披风的时候出错了：\n'+json.cape.msg);
                }
            },
            error: function(json) {
                showCallout('callout-danger', '出错啦，请联系作者！<br />详细信息：'+json.responseText);
            }
        });
    } else {
        showCallout('callout-warning', '你还没有选择任何文件哦');
    }
});
