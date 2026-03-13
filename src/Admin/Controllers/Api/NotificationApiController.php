<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers\Api;

use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Repositories\NotificationTemplateRepository;
use Appsolutely\AIO\Services\NotificationRuleService;
use Appsolutely\AIO\Services\NotificationService;
use Illuminate\Http\JsonResponse;

final class NotificationApiController extends AdminBaseApiController
{
    public function __construct(
        protected NotificationTemplateRepository $templateRepository,
        protected NotificationRuleRepository $ruleRepository,
        protected NotificationQueueRepository $queueRepository,
        protected NotificationService $notificationService,
        protected NotificationRuleService $ruleService
    ) {}

    /**
     * Process the notification queue
     */
    public function processQueue(): JsonResponse
    {
        try {
            $processed = $this->notificationService->processQueue();

            return $this->success(
                ['processed' => $processed],
                __t('Queue processing completed. :count notifications processed.', ['count' => $processed])
            );
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to process queue: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Retry failed notifications
     */
    public function retryFailed(): JsonResponse
    {
        try {
            $retried = $this->queueRepository->retryFailed();

            return $this->success(
                ['retried' => $retried],
                __t(':count failed notifications queued for retry.', ['count' => $retried])
            );
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to retry notifications: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Clean old sent notifications
     */
    public function cleanOld(): JsonResponse
    {
        try {
            $cleaned = $this->queueRepository->cleanOldSent();

            return $this->success(
                ['cleaned' => $cleaned],
                __t(':count old notifications cleaned.', ['count' => $cleaned])
            );
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to clean old notifications: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Retry a specific notification
     */
    public function retry(int $id): JsonResponse
    {
        try {
            $notification = $this->queueRepository->find($id);

            if (! $notification) {
                return $this->error(__t('Notification not found'), 404);
            }

            if ($notification->status !== 'failed') {
                return $this->error(__t('Only failed notifications can be retried'), 400);
            }

            $this->queueRepository->retry($id);

            return $this->success(
                ['id' => $id],
                __t('Notification queued for retry')
            );
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to retry notification: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Cancel a pending notification
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $notification = $this->queueRepository->find($id);

            if (! $notification) {
                return $this->error(__t('Notification not found'), 404);
            }

            if (! in_array($notification->status, ['pending', 'processing'])) {
                return $this->error(__t('Only pending or processing notifications can be cancelled'), 400);
            }

            $this->queueRepository->cancel($id);

            return $this->success(
                ['id' => $id],
                __t('Notification cancelled')
            );
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to cancel notification: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Duplicate a notification template
     */
    public function duplicateTemplate(int $id): JsonResponse
    {
        try {
            $template = $this->templateRepository->find($id);

            if (! $template) {
                return $this->error(__t('Template not found'), 404);
            }

            if ($template->is_system) {
                return $this->error(__t('System templates cannot be duplicated'), 400);
            }

            $duplicate = $this->templateRepository->duplicate($id);

            return $this->success(
                ['id' => $duplicate->id, 'name' => $duplicate->name],
                __t('Template duplicated successfully')
            );
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to duplicate template: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Test a notification rule
     */
    public function testRule(int $id): JsonResponse
    {
        try {
            $rule = $this->ruleRepository->find($id);

            if (! $rule) {
                return $this->error(__t('Rule not found'), 404);
            }

            $result = $this->ruleService->testRuleById($id);

            return $this->success(
                $result,
                __t('Rule test completed')
            );
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to test rule: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Preview a notification
     */
    public function preview(int $id): JsonResponse
    {
        try {
            $notification = $this->queueRepository->find($id);

            if (! $notification) {
                return $this->error(__t('Notification not found'), 404);
            }

            $content = $this->buildPreviewModal($notification);

            return $this->success(['content' => $content]);
        } catch (\Exception $e) {
            return $this->failServer(__t('Failed to load preview: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Build preview modal content
     */
    protected function buildPreviewModal(NotificationQueue $notification): string
    {
        $html = '<div class="modal-content">';
        $html .= '<div class="modal-header">';
        $html .= '<h4 class="modal-title">' . __t('Notification Preview') . '</h4>';
        $html .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $html .= '</div>';

        $html .= '<div class="modal-body">';
        $html .= '<div class="row">';
        $html .= '<div class="col-md-6">';
        $html .= '<h5>' . __t('Details') . '</h5>';
        $html .= '<table class="table table-sm">';
        $html .= '<tr><td><strong>' . __t('To') . ':</strong></td><td>' . $notification->recipient_email . '</td></tr>';
        $html .= '<tr><td><strong>' . __t('Subject') . ':</strong></td><td>' . $notification->subject . '</td></tr>';
        $html .= '<tr><td><strong>' . __t('Status') . ':</strong></td><td>' . $notification->status . '</td></tr>';
        $html .= '<tr><td><strong>' . __t('Created') . ':</strong></td><td>' . $notification->created_at->format('Y-m-d H:i:s') . '</td></tr>';
        if ($notification->sent_at) {
            $html .= '<tr><td><strong>' . __t('Sent') . ':</strong></td><td>' . $notification->sent_at->format('Y-m-d H:i:s') . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        $html .= '<div class="col-md-6">';
        $html .= '<h5>' . __t('Content') . '</h5>';
        $html .= '<div class="border p-3" style="max-height: 300px; overflow-y: auto;">';
        $html .= $notification->html_body ?: nl2br($notification->text_body);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-secondary" data-dismiss="modal">' . __t('Close') . '</button>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
