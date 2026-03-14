<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Models\Concerns\LocalizesDateTime;
use Appsolutely\AIO\Models\Concerns\UnsetsUnderscoreAttributes;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use LocalizesDateTime;
    use UnsetsUnderscoreAttributes;

    /**
     * Resolve the user model class from config.
     *
     * @return class-string
     */
    public static function userModel(): string
    {
        return config('aio.models.user', \App\Models\User::class);
    }
}
