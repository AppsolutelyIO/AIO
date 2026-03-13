<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Models\Concerns\LocalizesDateTime;
use Appsolutely\AIO\Models\Concerns\UnsetsUnderscoreAttributes;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use LocalizesDateTime;
    use UnsetsUnderscoreAttributes;
}
