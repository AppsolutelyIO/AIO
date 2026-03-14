<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Stub User model for package testing.
 * Simulates the host application's User model.
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $guarded = [];
}
