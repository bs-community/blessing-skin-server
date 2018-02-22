@extends('admin.master')

@section('title', trans('general.dashboard'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.dashboard') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <a href="{{ url('admin/users') }}">
                                <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('admin.index.total-users') }}</span>
                                    <span class="info-box-number">{{ App\Models\User::count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <a href="{{ url('admin/players') }}">
                                <span class="info-box-icon bg-green"><i class="fa fa-gamepad"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('admin.index.total-players') }}</span>
                                    <span class="info-box-number">{{ App\Models\Player::count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-aqua" style="background-color: #605ca8 !important;"><i class="fa fa-files-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('admin.index.total-textures') }}</span>
                        <span class="info-box-number">{{ App\Models\Texture::count() }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->

                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-hdd-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('admin.index.disk-usage') }}</span>
                        <?php $size = DB::table('textures')->sum('size') ?: 0; ?>
                        <span class="info-box-number">{{ $size > 1024 ? round($size / 1024, 1)."MB" : $size."KB" }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('admin.index.overview') }}</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="chart">
                            <canvas id="areaChart" style="height:250px"></canvas>
                        </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript" src="{{ assets('js/Chart.min.js') }}"></script>
@endsection

@section('script')
<?php
    $today = Carbon\Carbon::today()->timestamp;

    $data   = [];
    $labels = [];

    // Prepare data for the graph
    for ($i = 6; $i >= 0; $i--) {
        $time = Carbon\Carbon::createFromTimestamp($today - $i * 86400);

        $labels[] = $time->format('m-d');
        $data['user_register'][]  = App\Models\User::like('register_at',  $time->toDateString())->count();
        $data['texture_upload'][] = App\Models\Texture::like('upload_at', $time->toDateString())->count();
    }
?>
<script>
    $(function () {
        var areaChartCanvas = $("#areaChart").get(0).getContext("2d");
        var areaChart = new Chart(areaChartCanvas);

        var areaChartData = {
            labels: {!! json_encode($labels) !!},
            datasets: [
                {
                    label: trans("admin.textureUploads"),
                    fillColor: "rgba(210, 214, 222, 1)",
                    strokeColor: "rgba(210, 214, 222, 1)",
                    pointColor: "rgba(210, 214, 222, 1)",
                    pointStrokeColor: "#c1c7d1",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: {!! json_encode($data['texture_upload']) !!}
                },
                {
                    label: trans("admin.userRegistration"),
                    fillColor: "rgba(60,141,188,0.9)",
                    strokeColor: "rgba(60,141,188,0.8)",
                    pointColor: "#3b8bba",
                    pointStrokeColor: "rgba(60,141,188,1)",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(60,141,188,1)",
                    data: {!! json_encode($data['user_register']) !!}
                }
            ]
        };

        var areaChartOptions = {
            showScale: true,
            scaleShowGridLines: false,
            scaleGridLineColor: "rgba(0,0,0,.05)",
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: true,
            bezierCurve: true,
            bezierCurveTension: 0.3,
            pointDot: false,
            pointDotRadius: 4,
            pointDotStrokeWidth: 1,
            pointHitDetectionRadius: 20,
            datasetStroke: true,
            datasetStrokeWidth: 2,
            datasetFill: true,
            legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
            maintainAspectRatio: true,
            responsive: true
        };

        areaChart.Line(areaChartData, areaChartOptions);
    });
</script>
@endsection
