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
        <div class="breadcrumb"></div>
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
                                    <span class="info-box-number">{{ App\Models\User::all()->count() }}</span>
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
                                    <span class="info-box-number">{{ App\Models\Player::all()->count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-aqua" style="background-color: #605ca8 !important;"><i class="fa fa-files-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('admin.index.total-textures') }}</span>
                        <span class="info-box-number">{{ \Database::table('textures')->getRecordNum() }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->

                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-hdd-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('admin.index.disk-usage') }}</span>
                        <?php $size = \Database::table('textures')->fetchArray("SELECT SUM(`size`) AS total_size FROM `{table}` WHERE 1")['total_size'] ?: 0; ?>
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

<script type="text/javascript" src="{{ assets('vendor/Chart.min.js') }}"></script>

@endsection

@section('script')
<script>
<?php
    $today = Carbon\Carbon::today()->timestamp;

    $labels = [];
    $data   = [];

    for ($i = 6; $i >= 0; $i--) {
        $time = Carbon\Carbon::createFromTimestamp($today - $i * 86400);

        $labels[] = $time->format('m-d');
        $data['user_register'][]  = App\Models\User::like('register_at', $time->toDateString())->count();
        $data['texture_upload'][] = App\Models\Texture::like('upload_at', $time->toDateString())->count();
    }
?>
    $(function() {
        // Get context with jQuery - using jQuery's .get() method.
        var areaChartCanvas = $("#areaChart").get(0).getContext("2d");
        // This will get the first returned node in the jQuery collection.
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
          //Boolean - If we should show the scale at all
          showScale: true,
          //Boolean - Whether grid lines are shown across the chart
          scaleShowGridLines: false,
          //String - Colour of the grid lines
          scaleGridLineColor: "rgba(0,0,0,.05)",
          //Number - Width of the grid lines
          scaleGridLineWidth: 1,
          //Boolean - Whether to show horizontal lines (except X axis)
          scaleShowHorizontalLines: true,
          //Boolean - Whether to show vertical lines (except Y axis)
          scaleShowVerticalLines: true,
          //Boolean - Whether the line is curved between points
          bezierCurve: true,
          //Number - Tension of the bezier curve between points
          bezierCurveTension: 0.3,
          //Boolean - Whether to show a dot for each point
          pointDot: false,
          //Number - Radius of each point dot in pixels
          pointDotRadius: 4,
          //Number - Pixel width of point dot stroke
          pointDotStrokeWidth: 1,
          //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
          pointHitDetectionRadius: 20,
          //Boolean - Whether to show a stroke for datasets
          datasetStroke: true,
          //Number - Pixel width of dataset stroke
          datasetStrokeWidth: 2,
          //Boolean - Whether to fill the dataset with a color
          datasetFill: true,
          //String - A legend template
          legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
          //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
          maintainAspectRatio: true,
          //Boolean - whether to make the chart responsive to window resizing
          responsive: true
        };

        //Create the line chart
        areaChart.Line(areaChartData, areaChartOptions);
    });
</script>
@endsection
