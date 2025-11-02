<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DocumentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class DocumentAccessProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DocumentAccessRequest $accessRequest,
        private readonly string $status,
        private readonly ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'document_access_' . $this->status,
            'access_request_id' => $this->accessRequest->id,
            'document_title' => $this->accessRequest->documentVersion->document->title,
            'status' => $this->status,
            'reason' => $this->reason,
        ];
    }
}


