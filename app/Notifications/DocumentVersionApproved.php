<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DocumentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class DocumentVersionApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DocumentVersion $version
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Document Version Approved')
            ->greeting('Hello!')
            ->line("Document version has been approved: {$this->version->document->title}")
            ->line("Version: {$this->version->version_number}")
            ->action('View Document', route('documents.show', $this->version->document))
            ->line('The document is now available for access.');
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
