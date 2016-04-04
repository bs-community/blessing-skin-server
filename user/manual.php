<?php
/**
 * @Author: printempw
 * @Date:   2016-04-03 12:15:35
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-04 08:10:23
 */
require "../libraries/session.inc.php";
$data['style'] = <<< 'EOT'
<link rel="stylesheet" href="../assets/css/user.style.css">
<link rel="stylesheet" href="../assets/libs/highlight/styles/arduino-light.css">
<style>
pre { border: 0; }
td[class='key'], td[class='value'] { border-top: 0 !important; }
</style>
EOT;
$data['user'] = $user;
$data['page_title'] = "使用说明";
View::show('header', $data);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            使用说明
            <small>Manual</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">MOD 需求</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>本站支持的皮肤 MOD 有 <a href="http://www.mcbbs.net/forum.php?mod=viewthread&tid=358932" target="_blank">UniSkinMod</a>，<a href="http://www.mcbbs.net/thread-269807-1-1.html" target="_blank">CustomSkinLoader</a> 各自的新版和旧版，以及任何支持传统皮肤加载链接的皮肤 MOD。</p>
                        <p>详细教程：<a href="https://github.com/printempw/blessing-skin-server#客户端配置" target="_blank">@GitHub</a></p>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">配置生成</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">MOD</td>
                                    <td class="value">
                                       <select class="form-control" id="mod-select">
                                            <option value="csl">Custom Skin Loader</option>
                                            <option value="usm">Universal Skin Mod</option>
                                       </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="key">版本</td>
                                    <td class="value">
                                       <select class="form-control" id="version-select">
                                            <option value="13_1-upper">13.1 版及以上（推荐）</option>
                                            <option value="13_1-lower">13.1 版以下</option>
                                       </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">配置文件</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">

<pre id="config-13_1-upper">
{
    "enable": true,
    "loadlist": [
        {
            "name": "<?php echo Option::get('site_name'); ?>",
            "type": "CustomSkinAPI",
            "root": "<?php echo Option::get('site_url')."/csl/"; ?>"
        },
        {
            "name": "Mojang",
            "type": "MojangAPI"
        }
    ]
}
</pre>

<pre id="config-13_1-lower" class="hljs ini" style="display: none;">
# skinurls.txt
<?php echo Option::get('site_url'); ?>/skin/*.png
http://skins.minecraft.net/MinecraftSkins/*.png

# capeurls.txt
<?php echo Option::get('site_url'); ?>/cape/*.png
</pre>

<pre id="config-1_4-upper" style="display: none;">
{
    "rootURIs": [
        "<?php echo Option::get('site_url'); ?>/usm",
        "http://www.skinme.cc/uniskin"
    ],
    "legacySkinURIs": [],
    "legacyCapeURIs": []
}
</pre>

<pre id="config-1_2-1_3" class="hljs ini" style="display: none;">
# <?php echo Option::get('site_name')."\n"; ?>
Root: <?php echo Option::get('site_url'); ?>/usm
</pre>

<pre id="config-1_2-lower" class="hljs ini" style="display: none;">
# <?php echo Option::get('site_name')."\n"; ?>
Skin: <?php echo Option::get('site_url'); ?>/skin/%s.png
Cape: <?php echo Option::get('site_url'); ?>/cape/%s.png
# Mojang
Skin: http://skins.minecraft.net/MinecraftSkins/%s.png
Cape: http://skins.minecraft.net/MinecraftCloaks/%s.png
</pre>

                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
$data['script'] = <<< 'EOT'
<script type="text/javascript" src="../assets/libs/highlight/highlight.min.js"></script>
<script type="text/javascript" src="../assets/js/manual.utils.js"></script>
EOT;
View::show('footer', $data); ?>
