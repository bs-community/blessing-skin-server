@if (file_exists(resource_path($path = 'lang/overrides/'.config('app.locale').'/locale.js')))
    <script src="{{ url('resources/'.$path) }}"></script>
@endif
@if (file_exists(resource_path($path = 'lang/overrides/'.config('app.fallback_locale').'/locale.js')))
    <script src="{{ url('resources/'.$path) }}"></script>
@endif
<script src="{{ webpack_assets('index.js') }}"></script>
<script>{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
