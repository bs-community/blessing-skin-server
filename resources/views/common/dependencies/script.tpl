<script type="text/javascript" src="{{ assets('js/app.js') }}"></script>
<script type="text/javascript" src="{{ assets('lang/'.config('app.locale').'/locale.js') }}"></script>

@if (isset($module))
    <script type="text/javascript" src="{{ assets('js/'.$module.'.js') }}"></script>
@endif

<!-- User custom scripts -->
<script type="text/javascript">{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
