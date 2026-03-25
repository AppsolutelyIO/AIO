<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Admin\Metrics\ClearCache;
use Appsolutely\AIO\Http\Controllers\Dashboard;
use Appsolutely\AIO\Layout\Column;
use Appsolutely\AIO\Layout\Content;
use Appsolutely\AIO\Layout\Row;

final class HomeController extends AdminBaseController
{
    public function index(Content $content): Content
    {
        return $content
            ->header('Dashboard')
            ->body(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->row(Dashboard::title());
                });

                $row->column(6, new ClearCache());
            });
    }
}
