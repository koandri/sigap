<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FormRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class FormRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly FormRequest $formRequest,
        private readonly string $oldStatus,
        private readonly string $newStatus
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'form_request_status_changed',
            'form_request_id' => $this->formRequest->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'requested_by' => $this->formRequest->requestedBy->name,
        ];
    }
}
