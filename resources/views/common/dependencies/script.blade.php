@if (file_exists(public_path($path = 'app/langs/'.config('app.locale').'.js')))
    <script src="{{ url($path) }}"></script>
    @if (file_exists(resource_path($path = 'lang/overrides/'.config('app.locale').'/locale.js')))
        <script src="{{ url('resources/'.$path) }}"></script>
    @endif
@else
    <script src="{{ url('app/langs/'.config('app.fallback_locale').'.js') }}"></script>
    @if (file_exists(resource_path($path = 'lang/overrides/'.config('app.fallback_locale').'/locale.js')))
        <script src="{{ url('resources/'.$path) }}"></script>
    @endif
@endif

<script src="{{ webpack_assets('index.js') }}"></script>

<!-- User custom scripts -->
<script>{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
