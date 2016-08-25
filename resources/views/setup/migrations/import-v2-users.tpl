@extends('setup.migrations.master')

@section('content')

<?php $step = isset($_GET['step']) ? $_GET['step'] : '1'; ?>

{{-- Step 1: --}}

@if ($step == '1')
<h1>导入用户数据</h1>

<p>本功能用于导入 v2 的用户账户数据至 v3，请先将 v2 的 users 表改名导入到当前 v3 的同一数据库中</p>
<p>仅导入用户数据将会丢失用户的材质信息，如需保存原来的材质信息，请 <a href="index.php?action=import-v2-both">同时导入用户和材质</a>。</p>
<p><b>注意：</b> v3 当前设置的密码加密方式必须和之前 v2 的一致，否则导入后的用户将无法登录。</p>

<hr />

<form id="setup" method="post" action="index.php?action=import-v2-users&step=2" novalidate="novalidate">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="v2_table_name">v2 的用户表名</label></th>
            <td>
                <input name="v2_table_name" type="v2_table_name" id="v2_table_name" size="25" value="" />
                <p>就是你改名过的 v2 的 users 表现在的名字</p>
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
    if (Validate::checkPost(['v2_table_name'], true)) {
        if ($_POST['v2_table_name'] == "") {
            Http::redirect('index.php?action=import-v2-users&step=1', 'v2 users 表名不能为空');
        } else {
            if (Utils::convertString($_POST['v2_table_name']) != $_POST['v2_table_name'])
                Http::redirect('index.php?action=import-v2-users&step=1', "表名 {$_POST['v2_table_name']} 中含有无效字符");

            if (!DB::hasTable($_POST['v2_table_name'])) {
                Http::redirect('index.php?action=import-v2-users&step=1', "数据表 {$_POST['v2_table_name']} 不存在");
            }
        }
    } else {
        Http::redirect('index.php?action=import-v2-users&step=1', '表单信息不完整');
    }
?>

<h1>导入成功</h1>

<?php $result = Migration::importV2Users(); ?>

<p>已导入 {{ $result['imported'] }} 个用户，{{ $result['duplicated'] }} 个用户因重复而未导入。</p>

<p class="step">
<a href="../../" class="button button-large">导入完成</a>
</p>

@endif

@endsection
