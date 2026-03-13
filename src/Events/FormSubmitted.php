<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Events;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a form is submitted
 */
final class FormSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Form $form,
        public readonly FormEntry $entry,
        public readonly array $data
    ) {}
}
