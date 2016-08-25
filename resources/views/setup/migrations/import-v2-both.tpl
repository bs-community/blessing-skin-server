@extends('setup.migrations.master')

@section('content')

<?php $step = isset($_GET['step']) ? $_GET['step'] : '1'; ?>

{{-- Step 1: --}}

@if ($step == '1')
<h1>同时导入用户数据以及用户材质</h1>

<p>将同时导入用户数据以及材质，逻辑比单独导入更加完善。</p>
<p>导入后材质的上传者将被设置为 v2 的原用户，上传时间将被设置为 v2 用户的最后修改时间。导入后的材质会被自动添加至原上传者的衣柜中，并应用至其所属角色。</p>
<p><b>注意：</b> 请先将 v2 的 users 表改名导入到当前 v3 的同一数据库中</p>

<hr />

<form id="setup" method="post" action="index.php?action=import-v2-both&step=2" novalidate="novalidate">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="v2_table_name">v2 的用户表名</label></th>
            <td>
                <input name="v2_table_name" type="v2_table_name" id="v2_table_name" size="25" value="" />
                <p>就是你改名过的 v2 的 users 表现在的名字</p>
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
    if (Validate::checkPost(['v2_table_name', 'texture_name_pattern'], true)) {
        if ($_POST['v2_table_name'] == "") {
            Http::redirect('index.php?action=import-v2-both&step=1', 'v2 users 表名不能为空');
        } else {
            if (Utils::convertString($_POST['v2_table_name']) != $_POST['v2_table_name'])
                Http::redirect('index.php?action=import-v2-both&step=1', "表名 {$_POST['v2_table_name']} 中含有无效字符");

            if (!DB::hasTable($_POST['v2_table_name'])) {
                Http::redirect('index.php?action=import-v2-both&step=1', "数据表 {$_POST['v2_table_name']} 不存在");
            }
        }
    } else {
        Http::redirect('index.php?action=import-v2-both&step=1', '表单信息不完整');
    }

?>

<h1>导入成功</h1>

<?php $result = Migration::importV2Both(); ?>

<p>已导入 {{ $result['user']['imported'] }} 个用户，{{ $result['user']['duplicated'] }} 个用户因重复而未导入。</p>
<p>已导入 {{ $result['texture']['imported'] }} 个材质到皮肤库，{{ $result['texture']['duplicated'] }} 个材质因重复而未导入。</p>

<p class="step">
<a href="../../" class="button button-large">导入完成</a>
</p>

@endif

@endsection
