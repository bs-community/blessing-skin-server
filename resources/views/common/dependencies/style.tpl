<!-- Bundled styles -->
<link rel="stylesheet" href="{{ url('public/style.css') }}">
<!-- AdminLTE color scheme -->
<link rel="stylesheet" href="{{ url('public/skins/'.option('color_scheme').'.min.css') }}">

@if (isset($module))
    <link rel="stylesheet" href="{{ url('public/'.$module.'.css') }}">
@endif

<!-- User custom styles -->
<style>{!! option('custom_css') !!}</style>
{{-- Content added by plugins dynamically --}}
{!! bs_header_extra() !!}
