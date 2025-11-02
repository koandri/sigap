<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DocumentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class DocumentVersionApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DocumentVersion $version
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'document_version_approved',
            'document_version_id' => $this->version->id,
            'document_title' => $this->version->document->title,
            'version_number' => $this->version->version_number,
        ];
    }
}
