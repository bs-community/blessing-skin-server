<!-- Bundled styles -->
@if (app()->environment('development'))
    <script src="{{ webpack_assets('style.js') }}"></script>
@endif
<link rel="stylesheet" href="{{ webpack_assets('style.css') }}">
<!-- AdminLTE color scheme -->
<link rel="stylesheet" href="{{ webpack_assets('skins/'.option('color_scheme').'.min.css') }}">

@if (isset($module))
    <link rel="stylesheet" href="{{ webpack_assets($module . '.css') }}">
@endif

<!-- User custom styles -->
<style>{!! option('custom_css') !!}</style>
{{-- Content added by plugins dynamically --}}
{!! bs_header_extra() !!}
