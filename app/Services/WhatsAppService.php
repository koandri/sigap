<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class WhatsAppService
{
    private string $baseUrl;
    private string $session;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://waha.suryagroup.app/api';
        $this->session = 'ptsiap';
        $this->apiKey = env('WAHA_API_KEY', '');
    }

    /**
     * Send WhatsApp text message
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        try {
            $response = Http::acceptJson()
                ->withHeader('X-Api-Key', $this->apiKey)
                ->post("{$this->baseUrl}/sendText", [
                    'chatId' => $chatId,
                    'reply_to' => null,
                    'text' => $text,
                    'linkPreview' => true,
                    'linkPreviewHighQuality' => false,
                    'session' => $this->session,
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'chatId' => $chatId,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp message', [
                'chatId' => $chatId,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp message exception', [
                'chatId' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp image
     */
    public function sendImage(
        string $chatId,
        string $fileUrl,
        string $filename,
        string $mimetype = 'image/jpeg',
        ?string $caption = null
    ): bool {
        try {
            $response = Http::acceptJson()
                ->withHeader('X-Api-Key', $this->apiKey)
                ->post("{$this->baseUrl}/sendImage", [
                    'chatId' => $chatId,
                    'file' => [
                        'mimetype' => $mimetype,
                        'filename' => $filename,
                        'url' => $fileUrl,
                    ],
                    'reply_to' => null,
                    'caption' => $caption,
                    'session' => $this->session,
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp image sent successfully', [
                    'chatId' => $chatId,
                    'filename' => $filename,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp image', [
                'chatId' => $chatId,
                'filename' => $filename,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp image exception', [
                'chatId' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp file
     */
    public function sendFile(
        string $chatId,
        string $fileUrl,
        string $filename,
        string $mimetype,
        ?string $caption = null
    ): bool {
        try {
            $response = Http::acceptJson()
                ->withHeader('X-Api-Key', $this->apiKey)
                ->post("{$this->baseUrl}/sendFile", [
                    'chatId' => $chatId,
                    'file' => [
                        'mimetype' => $mimetype,
                        'filename' => $filename,
                        'url' => $fileUrl,
                    ],
                    'reply_to' => null,
                    'caption' => $caption,
                    'session' => $this->session,
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp file sent successfully', [
                    'chatId' => $chatId,
                    'filename' => $filename,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp file', [
                'chatId' => $chatId,
                'filename' => $filename,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp file exception', [
                'chatId' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send to development chat (from env)
     */
    public function sendToDevChat(string $text): bool
    {
        $chatId = env('DEV_WHATSAPP_CHAT_ID');

        if (!$chatId) {
            Log::warning('DEV_WHATSAPP_CHAT_ID not configured');
            return false;
        }

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send image to development chat
     */
    public function sendImageToDevChat(
        string $fileUrl,
        string $filename,
        string $mimetype = 'image/jpeg',
        ?string $caption = null
    ): bool {
        $chatId = env('DEV_WHATSAPP_CHAT_ID');

        if (!$chatId) {
            Log::warning('DEV_WHATSAPP_CHAT_ID not configured');
            return false;
        }

        return $this->sendImage($chatId, $fileUrl, $filename, $mimetype, $caption);
    }

    /**
     * Send file to development chat
     */
    public function sendFileToDevChat(
        string $fileUrl,
        string $filename,
        string $mimetype,
        ?string $caption = null
    ): bool {
        $chatId = env('DEV_WHATSAPP_CHAT_ID');

        if (!$chatId) {
            Log::warning('DEV_WHATSAPP_CHAT_ID not configured');
            return false;
        }

        return $this->sendFile($chatId, $fileUrl, $filename, $mimetype, $caption);
    }

    /**
     * Send to multiple recipients
     */
    public function sendToMultiple(array $chatIds, string $text): array
    {
        $results = [];

        foreach ($chatIds as $chatId) {
            $results[$chatId] = $this->sendMessage($chatId, $text);
        }

        return $results;
    }
}

