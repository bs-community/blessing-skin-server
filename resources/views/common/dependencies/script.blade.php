@inject('intl', 'App\Services\Translations\JavaScript')
<script src="{{ $intl->generate(app()->getLocale()) }}"></script>
<script src="{{ $intl->plugin(app()->getLocale()) }}"></script>
<script src="{{ webpack_assets('index.js') }}"></script>
<script>{!! option('custom_js') !!}</script>
{{-- Content added by plugins dynamically --}}
{!! bs_footer_extra() !!}
