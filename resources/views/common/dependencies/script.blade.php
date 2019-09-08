@inject('intl', 'App\Services\Translations\JavaScript')
<script src="{{ $intl->generate(app()->getLocale()) }}"></script>
@if ($pluginI18n = $intl->plugin(app()->getLocale()))
    <script src="{{ $pluginI18n }}"></script>
@endif
<script src="{{ webpack_assets('index.js') }}"></script>
<script>{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
