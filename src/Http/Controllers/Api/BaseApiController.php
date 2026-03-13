<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers\Api;

use Appsolutely\AIO\Traits\ApiResponseTrait;
use Illuminate\Routing\Controller as BaseController;

class BaseApiController extends BaseController
{
    use ApiResponseTrait;
}
