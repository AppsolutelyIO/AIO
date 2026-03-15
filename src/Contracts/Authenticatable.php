<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Contracts;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * Contract for the User model used by AIO package.
 *
 * Host applications should ensure their User model implements this interface.
 * The concrete class is resolved via config('aio.models.user').
 */
interface Authenticatable extends AuthenticatableContract {}
