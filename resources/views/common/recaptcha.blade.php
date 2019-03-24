@if (option('recaptcha_sitekey'))
<script
    src="https://www.{{ option('recaptcha_mirror')
        ? 'recaptcha.net'
        : 'google.com'
    }}/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit"
    async
    defer
>
</script>
@endif
