<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PushoverService
{
    private string $apiUrl;
    private string $appToken;
    private string $userToken;

    public function __construct()
    {
        $this->apiUrl = 'https://api.pushover.net/1/messages.json';
        $this->appToken = env('PUSHOVER_APP_TOKEN', '');
        $this->userToken = env('PUSHOVER_USER_TOKEN', '');
    }

    /**
     * Send high-priority notification via Pushover
     */
    public function sendNotification(string $title, string $message): bool
    {
        if (empty($this->appToken) || empty($this->userToken)) {
            Log::warning('Pushover tokens not configured');
            return false;
        }

        try {
            $response = Http::asForm()->post($this->apiUrl, [
                'token' => $this->appToken,
                'user' => $this->userToken,
                'title' => $title,
                'message' => $message,
                'priority' => 2,
                'html' => 1,
            ]);

            if ($response->successful()) {
                Log::info('Pushover notification sent successfully', [
                    'title' => $title,
                ]);
                return true;
            }

            Log::error('Failed to send Pushover notification', [
                'title' => $title,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Pushover notification exception', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp failure notification
     */
    public function sendWhatsAppFailureNotification(
        string $failureType,
        string $recipient,
        string $originalMessage
    ): bool {
        $title = 'WhatsApp Notification Failed';
        
        $message = '<b>WhatsApp Notification Failure</b><br><br>';
        $message .= '<b>Notification Type:</b> ' . htmlspecialchars($failureType) . '<br>';
        $message .= '<b>Intended Recipient:</b> ' . htmlspecialchars($recipient) . '<br>';
        $message .= '<b>Time:</b> ' . now()->format('Y-m-d H:i:s') . '<br><br>';
        $message .= '<b>Original Message:</b><br>';
        $message .= '<i>' . nl2br(htmlspecialchars($originalMessage)) . '</i>';

        return $this->sendNotification($title, $message);
    }
}

