<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DocumentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Document Access Request ' . ucfirst($this->status))
            ->greeting('Hello!');

        if ($this->status === 'approved') {
            $message->line('Your document access request has been approved.')
                ->line("Document: {$this->accessRequest->documentVersion->document->title}")
                ->line("Access type: {$this->accessRequest->getEffectiveAccessType()->label()}")
                ->action('View Document', route('documents.show', $this->accessRequest->documentVersion->document));
        } else {
            $message->line('Your document access request has been rejected.')
                ->line("Document: {$this->accessRequest->documentVersion->document->title}");
            
            if ($this->reason) {
                $message->line("Reason: {$this->reason}");
            }
        }

        return $message;
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

