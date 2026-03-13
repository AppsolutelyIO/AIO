<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class NotificationTemplate extends Model
{
    use HasFactory;
    use ScopeStatus;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_system',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_system' => 'boolean',
        'status'    => Status::class,
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(NotificationRule::class, 'template_id');
    }

    public function queueItems(): HasMany
    {
        return $this->hasMany(NotificationQueue::class, 'template_id');
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariablesAttribute(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Check if template can be deleted
     */
    public function getCanDeleteAttribute(): bool
    {
        return ! $this->is_system && $this->rules()->count() === 0;
    }

    /**
     * Get template usage count
     */
    public function getUsageCountAttribute(): int
    {
        return $this->rules()->status()->count();
    }

    /**
     * Render template with variables
     */
    public function render(array $variables): array
    {
        $subject  = $this->subject ?? '';
        $bodyHtml = $this->body_html ?? '';
        $bodyText = $this->body_text;

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $replacement = is_array($value) ? json_encode($value) : (string) $value;

            $subject  = str_replace($placeholder, $replacement, $subject);
            $bodyHtml = str_replace($placeholder, $replacement, $bodyHtml);
            if ($bodyText) {
                $bodyText = str_replace($placeholder, $replacement, $bodyText);
            }
        }

        return [
            'subject'   => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
        ];
    }
}
