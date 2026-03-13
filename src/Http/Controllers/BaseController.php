<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
