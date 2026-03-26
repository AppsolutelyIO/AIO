@if (config('forms.captcha.turnstile.enabled') && config('forms.captcha.turnstile.site_key'))
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    @endonce
@endif
