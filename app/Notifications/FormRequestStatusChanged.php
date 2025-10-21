<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FormRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Form Request Status Update')
            ->greeting('Hello!')
            ->line("Your form request status has been updated from {$this->oldStatus} to {$this->newStatus}.");

        if ($this->newStatus === 'ready_for_collection') {
            $message->action('View Request', route('form-requests.show', $this->formRequest))
                ->line('Your forms are ready for collection.');
        }

        return $message;
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
