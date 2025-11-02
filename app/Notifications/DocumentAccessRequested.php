<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DocumentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class DocumentAccessRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DocumentAccessRequest $accessRequest
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'document_access_requested',
            'access_request_id' => $this->accessRequest->id,
            'document_title' => $this->accessRequest->documentVersion->document->title,
            'requested_by' => $this->accessRequest->user->name,
            'access_type' => $this->accessRequest->access_type,
        ];
    }
}
