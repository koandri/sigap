<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class HomeAssistantService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = env('HA_BASE_URL', 'http://192.168.99.99:8123');
        $this->token = env('HA_TOKEN', '');
    }

    /**
     * Get temperature history from HomeAssistant
     *
     * @param string $entityId The entity ID (e.g., 'sensor.tes_temperature')
     * @param string|null $startTime Start time in ISO 8601 format (YYYY-MM-DDThh:mm:ssTZD)
     * @param string|null $endTime End time in ISO 8601 format (YYYY-MM-DDThh:mm:ssTZD)
     * @return array Raw API response data
     * @throws \Exception
     */
    public function getTemperatureHistory(
        string $entityId,
        ?string $startTime = null,
        ?string $endTime = null
    ): array {
        if (empty($this->token)) {
            throw new \Exception('HomeAssistant token (HA_TOKEN) is not configured');
        }

        try {
            $url = "{$this->baseUrl}/api/history/period";
            
            $params = [
                'filter_entity_id' => $entityId,
                'minimal_response' => '',
                'no_attributes' => '',
            ];

            if ($startTime !== null) {
                $params['start_time'] = $startTime;
            }

            if ($endTime !== null) {
                $params['end_time'] = $endTime;
            }

            $response = Http::timeout(30)
                ->withToken($this->token)
                ->get($url, $params);

            if (!$response->successful()) {
                Log::error('HomeAssistant API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'entity_id' => $entityId,
                ]);
                throw new \Exception("HomeAssistant API request failed with status: {$response->status()}");
            }

            $data = $response->json();

            if (!is_array($data)) {
                throw new \Exception('Invalid response format from HomeAssistant API');
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('HomeAssistant Service Error', [
                'entity_id' => $entityId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

