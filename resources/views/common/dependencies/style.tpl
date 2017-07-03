<!-- Bundled styles -->
<link rel="stylesheet" href="{{ assets('css/style.css') }}">
<!-- AdminLTE color scheme -->
<link rel="stylesheet" href="{{ assets('css/skins/'.option('color_scheme').'.min.css') }}">

@if (isset($module))
    <link rel="stylesheet" href="{{ assets('css/'.$module.'.css') }}">
@endif

<!-- User custom styles -->
<style>{!! option('custom_css') !!}</style>
{{-- Content added by plugins dynamically --}}
{!! bs_header_extra() !!}
