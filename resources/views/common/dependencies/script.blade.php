<script type="text/javascript" src="{{ assets('js/app.js') }}"></script>

@if (file_exists(resource_path($path = 'lang/'.config('app.locale').'/locale.js')))
<script type="text/javascript" src="{{ assets($path) }}"></script>
@else
<script type="text/javascript" src="{{ assets('lang/'.config('app.fallback_locale').'/locale.js') }}"></script>
@endif

@if (file_exists(resource_path($path = 'lang/overrides/'.config('app.locale').'/locale.js')))
<script type="text/javascript" src="{{ assets($path) }}"></script>
@endif

@if (isset($module))
    <script type="text/javascript" src="{{ assets('js/'.$module.'.js') }}"></script>
@endif

<!-- User custom scripts -->
<script type="text/javascript">{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
