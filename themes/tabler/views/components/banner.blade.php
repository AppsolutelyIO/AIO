@props(['style' => session('flash.bannerStyle', 'success'), 'message' => session('flash.banner')])

<div x-data="{{ json_encode(['show' => true, 'style' => $style, 'message' => $message]) }}"
    :class="{
        'bg-primary': style == 'success',
        'bg-danger': style == 'danger',
        'bg-warning': style == 'warning',
        'bg-secondary': style != 'success' && style != 'danger' && style != 'warning'
    }"
    style="display: none;" x-show="show && message"
    x-on:banner-message.window="
        style = event.detail.style;
        message = event.detail.message;
        show = true;
    ">
    <div class="container-xl">
        <div class="d-flex align-items-center justify-content-between py-2 text-white">
            <div class="d-flex align-items-center">
                <svg x-show="style == 'success'" xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-2"
                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                    <path d="M9 12l2 2l4 -4" />
                </svg>
                <svg x-show="style == 'danger'" xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-2"
                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 9v4" />
                    <path
                        d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                    <path d="M12 16h.01" />
                </svg>
                <span class="small" x-text="message"></span>
            </div>
            <button type="button" class="btn-close btn-close-white" aria-label="Close" x-on:click="show = false"></button>
        </div>
    </div>
</div>
