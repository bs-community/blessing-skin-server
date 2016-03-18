<?php
/**
 * @Author: prpr
 * @Date:   2016-01-21 13:56:40
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-18 14:44:02
 */
require "../includes/session.inc.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上传皮肤 - <?php echo Config::get('site_name'); ?></title>
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="../libs/pure/pure-min.css">
    <link rel="stylesheet" href="../libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="../libs/glyphicon/glyphicon.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.style.css">
    <link rel="stylesheet" href="../libs/ply/ply.css">
</head>
<body>
<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="<?php echo Config::get('site_url'); ?>">
            <?php echo Config::get('site_name'); ?>
        </a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item">
                <a class="pure-menu-link" href="profile.php">个人设置</a>
            </li>
            <?php include "../includes/welcome.inc.php"; ?>
        </ul>
        <div class="home-menu-blur">
            <div class="home-menu-wrp">
                <div class="home-menu-bg"></div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="pure-g">
        <div class="pure-u-md-1-2 pure-u-1">
            <div class="panel panel-default">
                <div class="panel-heading">上传</div>
                <div class="panel-body">
                    <div id="upload-form">
                        <p>选择皮肤：</p>
                        <input type="file" id="skininput" name="skininput" accept="image/png" /><br />
                        <p>选择披风：</p>
                        <input type="file" id="capeinput" name="capeinput" accept="image/png" /><br />
                        <input type="radio" id="model-steve" name="model" />
                        <label for="model-steve">我的皮肤适合传统 Steve 皮肤模型</label><br />
                        <input type="radio" id="model-alex" name="model" />
                        <label for="model-alex">我的皮肤适合新版 Alex 皮肤模型</label><br />
                        <br />
                        <button id="upload" class="pure-button pure-button-primary">确认上传</button>
                        <a id="preview" href="javascript:show2dPreview();" class="pure-button">2D 皮肤预览</a>
                    </div>
                    <div id="msg" class="alert hide" role="alert"></div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">修改优先皮肤模型</div>
                <div class="panel-body">
                    <p>你现在的优先皮肤模型是 <b><?php echo $user->getPreference(); ?></b>。
                        <a class="pure-button pure-button-default" style="margin: 0 3px;" href="javascript:changeModel();">更改</a>
                    </p>
                </div>
            </div>
        </div>
        <div class="pure-u-md-1-2 pure-u-1">
            <div class="panel panel-default">
                <div class="panel-heading">皮肤预览
                    <div class="operations">
                        <span title="Movements" class="glyphicon glyphicon-pause"></span>
                        <span title="Running" class="glyphicon glyphicon-forward"></span>
                        <span title="Rotation" class="glyphicon glyphicon-repeat"></span>
                    </div>
                </div>
                <div class="panel-body">
                    <?php include "preview.php"; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script type="text/javascript" src="../libs/jquery/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="../libs/ply/ply.min.js"></script>
<script type="text/javascript" src="../libs/cookie.js"></script>
<script type="text/javascript" src="../assets/js/utils.js"></script>
<script type="text/javascript" src="../assets/js/user.utils.js"></script>
<?php if ($user->getTexture('alex') && ($user->getTexture('steve') == "")) {?>
<script type="text/javascript">
    showMsg('alert-warning',
        '看起来你只上传了适合 Alex 模型的皮肤。注意 Minecraft 版本低于 1.8 的皮肤 MOD 不支持双层皮肤。'+
        '你最好再上传一个适用于 Steve 模型的皮肤，并将优先模型设置为 Alex 以获得在各个版本下的良好表现。');
</script>
<?php } ?>
</html>
