<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Metrics;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Traits\HasAuthorization;
use Appsolutely\AIO\Widgets\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ClearCache extends Card
{
    use HasAuthorization;

    public function __construct()
    {
        parent::__construct('Clear Cache', '');

        $this->withHeaderBorder();

        $this->content(
            '<div class="text-center p-2">'
            . '<button class="btn btn-primary btn-clear-cache">'
            . '<i class="feather icon-trash-2"></i> Clear All Cache'
            . '</button>'
            . '</div>'
        );
    }

    public function handle(Request $request): array
    {
        Artisan::call('responsecache:clear');
        Artisan::call('optimize:clear');
        Artisan::call('optimize');

        return [
            'status'  => 1,
            'content' => '<div class="text-center p-2"><span class="text-success"><i class="feather icon-check-circle"></i> Cache cleared and optimized successfully.</span></div>',
        ];
    }

    public function render(): string
    {
        $this->addScript();

        return parent::render();
    }

    protected function addScript(): void
    {
        $id  = $this->id();
        $url = route(admin_api_route_name('value'));
        $key = str_replace('\\', '\\\\', static::class);

        Admin::script(<<<JS
(function () {
    var \$card = $('#{$id}');
    var loading = false;

    \$card.on('click', '.btn-clear-cache', function () {
        if (loading) return;
        loading = true;
        \$card.loading();

        $.ajax({
            url: '{$url}',
            method: 'POST',
            dataType: 'json',
            data: {_key: '{$key}', _token: AIO.token},
            success: function (response) {
                loading = false;
                \$card.loading(false);
                \$card.find('.card-body').html(response.content);
                setTimeout(function () {
                    \$card.find('.card-body').html(
                        '<div class="text-center p-2"><button class="btn btn-primary btn-clear-cache"><i class="feather icon-trash-2"></i> Clear All Cache</button></div>'
                    );
                }, 3000);
            },
            error: function (a, b, c) {
                loading = false;
                \$card.loading(false);
                AIO.handleAjaxError(a, b, c);
            }
        });
    });
})();
JS);
    }
}
