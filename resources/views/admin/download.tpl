@extends('admin.master')

@section('title', '下载更新')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            下载更新
            <small>Download Updates</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">下载更新</h3>
            </div><!-- /.box-header -->
            <div class="box-body"> <?php
                $updater = new Updater(config('app.version'));

                if ($updater->newVersionAvailable()) {
                    $zip_path = $updater->downloadUpdate(false);

                    if ($zip_path === false) {
                        exit('<p>无法下载更新包。</p>');
                    }

                    $zip = new ZipArchive();
                    $extract_dir = BASE_DIR."/setup/update_cache/{$updater->latest_version}";
                    $res = $zip->open($zip_path);

                    if ($res === true) {
                        echo "<p>正在解压更新包</p>";
                        $zip->extractTo($extract_dir);
                    } else {
                        exit('<p>更新包解压缩失败。错误代码：'.$res.'</p>');
                    }
                    $zip->close();

                    if (Storage::copyDir($extract_dir, BASE_DIR) !== true) {
                        Storage::removeDir(BASE_DIR.'/setup/update_cache/');
                        exit('无法覆盖文件。');
                    } else {
                       echo "<p>正在覆盖文件</p>";
                       Storage::removeDir(BASE_DIR.'/setup/update_cache/');
                       echo "<p>正在清理</p>";
                    }
                    echo "<p>更新完成。</p>";
                } else {
                    echo "<p>无可用更新。</p>";
                } ?>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <a href="../setup/update.php" class="btn btn-primary">下一步</a>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
