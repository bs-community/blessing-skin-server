@if (file_exists(public_path($path = 'langs/'.config('app.locale').'.js')))
    <script src="{{ url('public/'.$path) }}"></script>
    @if (file_exists(resource_path($path = 'lang/overrides/'.config('app.locale').'/locale.js')))
        <script src="{{ url('resources/'.$path) }}"></script>
    @endif
@else
    <script src="{{ url('public/langs/'.config('app.fallback_locale').'.js') }}"></script>
    @if (file_exists(resource_path($path = 'lang/overrides/'.config('app.fallback_locale').'/locale.js')))
        <script src="{{ url('resources/'.$path) }}"></script>
    @endif
@endif

<script src="{{ webpack_assets('index.js') }}"></script>

<!-- User custom scripts -->
<script>{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
