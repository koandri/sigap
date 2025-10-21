<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DocumentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class DocumentAccessRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DocumentAccessRequest $accessRequest
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Document Access Request')
            ->greeting('Hello!')
            ->line('A new document access request has been submitted.')
            ->line("Document: {$this->accessRequest->documentVersion->document->title}")
            ->line("Requested by: {$this->accessRequest->user->name}")
            ->line("Access type: {$this->accessRequest->access_type}")
            ->action('Review Request', route('document-access-requests.pending'))
            ->line('Please review and approve or reject the request.');
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
