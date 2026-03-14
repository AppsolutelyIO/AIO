<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'address',
        'address_extra',
        'town',
        'city',
        'province',
        'postcode',
        'country',
        'note',
        'remark',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::userModel());
    }
}
