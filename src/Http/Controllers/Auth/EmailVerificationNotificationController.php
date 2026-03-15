<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers\Auth;

use Appsolutely\AIO\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EmailVerificationNotificationController extends BaseController
{
    /**
     * Send a new email verification notification.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
