@props([
    'componentId' => null,
    'wireModel' => 'turnstileToken',
    'theme' => 'light',
    'class' => '',
])

@if (config('forms.captcha.turnstile.enabled') && config('forms.captcha.turnstile.site_key'))
    <div {{ $attributes->merge(['class' => $class]) }}>
        <div id="turnstile-widget-{{ $componentId ?? $this->getId() }}" wire:ignore x-data x-init="const render = () => {
            if (typeof turnstile === 'undefined') {
                setTimeout(render, 100);
                return;
            }
            turnstile.render($el, {
                sitekey: '{{ config('forms.captcha.turnstile.site_key') }}',
                callback: (token) => { $wire.set('{{ $wireModel }}', token); },
                'expired-callback': () => { $wire.set('{{ $wireModel }}', ''); },
                theme: '{{ $theme }}',
            });
        };
        render();"></div>
    </div>
    @error('turnstile')
        <div class="text-danger text-center mt-2" style="font-size: 0.875rem;">{{ $message }}</div>
    @enderror
@endif
