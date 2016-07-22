@extends('admin.master')

@section('title', '仪表盘')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            仪表盘
            <small>Dashboard</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-6">


                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <a href="../admin/user">
                                <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">注册用户</span>
                                    <span class="info-box-number">{{ App\Models\UserModel::all()->count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <a href="../admin/player">
                                <div class="info-box-content" style="margin-left: 0;">
                                    <span class="info-box-text">角色总数</span>
                                    <span class="info-box-number">{{ App\Models\PlayerModel::all()->count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>
                </div>




                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-files-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">上传材质总数</span>
                        <span class="info-box-number">{{ \Storage::getFileNum(BASE_DIR."/textures/") }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->

                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-hdd-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">占用空间大小</span>
                        <span class="info-box-number">{{ floor(\Storage::getDirSize(BASE_DIR."/textures/")/1024)."KB" }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
