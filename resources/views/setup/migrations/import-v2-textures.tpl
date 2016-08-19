@extends('setup.migrations.master')

@section('content')

<?php $step = isset($_GET['step']) ? $_GET['step'] : '1'; ?>

{{-- Step 1: --}}

@if ($step == '1')
<h1>导入皮肤库</h1>

<p>本功能用于导入 v2 用户皮肤至 v3 的皮肤库</p>
<p>请先将 v2 的 users 表改名导入到当前 v3 的同一数据库中</p>

<form id="setup" method="post" action="index.php?action=import-v2-textures&step=2" novalidate="novalidate">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="v2_table_name">v2 的用户表名</label></th>
            <td>
                <input name="v2_table_name" type="v2_table_name" id="v2_table_name" size="25" value="" />
                <p>就是你改名过的 v2 的 users 表现在的名字</p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="uploader_uid">材质上传者 uid</label></th>
            <td>
                <input name="uploader_uid" type="text" id="uploader_uid" size="25" value="0" />
                <p>
                    <span class="description important">
                        导入后的材质在皮肤库中显示的上传者，填写 0 会显示为「不存在的用户」
                    </span>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="texture_name_pattern">导入后的材质名称</label></th>
            <td>
                <input name="texture_name_pattern" type="text" id="texture_name_pattern" size="25" value="{username} - {model}" />
                <p>
                    <span class="description important">
                        {username} 表示材质原本的上传者用户名，{model} 表示原来材质的模型
                    </span>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">私密材质</th>
            <td>
                <label for="import_as_private">
                    <input name="import_as_private" type="checkbox" id="import_as_private" size="25" /> 导入为私密材质
                </label>
            </td>
        </tr>
    </table>

    @if (isset($_SESSION['msg']))
    <div class="alert alert-warning" role="alert">{{ htmlspecialchars($_SESSION['msg']) }}</div>
    <?php unset($_SESSION['msg']); ?>
    @endif

    <p class="step">
        <input type="submit" name="submit" id="submit" class="button button-large" value="开始迁移"  />
    </p>
</form>
@endif

{{-- Step 2: --}}

@if ($step == '2')

<?php
    if (Validate::checkPost(['v2_table_name', 'uploader_uid', 'texture_name_pattern'], true)) {
        if ($_POST['v2_table_name'] == "") {
            Http::redirect('index.php?action=import-v2-textures&step=1', 'v2 users 表名不能为空');
        } else {
            $_POST['uploader_uid'] = ($_POST['uploader_uid'] == "") ? 0 : (int)$_POST['uploader_uid'];

            if (Utils::convertString($_POST['v2_table_name']) != $_POST['v2_table_name'])
                Http::redirect('index.php?action=import-v2-textures&step=1', "表名 {$_POST['v2_table_name']} 中含有无效字符");

            if (!Database::hasTable($_POST['v2_table_name'])) {
                Http::redirect('index.php?action=import-v2-textures&step=1', "数据表 {$_POST['v2_table_name']} 不存在");
            }
        }
    } else {
        Http::redirect('index.php?action=import-v2-textures&step=1', '表单信息不完整');
    }
?>

<h1>导入成功</h1>

<?php $result = Migration::importV2Textures(); ?>

<p>已导入 {{ $result['imported'] }} 个材质到皮肤库，{{ $result['duplicated'] }} 个材质因重复而未导入。</p>
<p>注意：请将 v2 的 textures 文件夹内容复制到 v3 的 textures 文件夹中</p>

<p class="step">
<a href="../../" class="button button-large">导入完成</a>
</p>

@endif

@endsection
