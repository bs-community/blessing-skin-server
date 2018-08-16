@if (file_exists(public_path('langs/'.config('app.locale').'.js')))
    <script src="{{ url('public/langs/'.config('app.locale').'.js') }}"></script>
@else
    <script src="{{ assets('lang/'.config('app.fallback_locale').'/locale.js') }}"></script>
@endif

@if (str_contains(request()->userAgent(), ['MSIE', 'Trident']))
    <script src="{{ url('public/polyfill.js') }}"></script>
@endif
<script src="{{ webpack_assets('index.js') }}"></script>

<!-- User custom scripts -->
<script>{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
