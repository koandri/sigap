<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class FirecrawlService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.firecrawl.api_key', '');
        $this->baseUrl = config('services.firecrawl.base_url', 'https://api.firecrawl.dev/v2');
    }

    /**
     * Search for specifications using manufacturer and model.
     *
     * @param string $manufacturer
     * @param string $model
     * @return array{success: bool, specifications?: array, error?: string}
     */
    public function searchSpecifications(string $manufacturer, string $model): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Firecrawl API key is not configured'
            ];
        }

        if (empty($manufacturer) || empty($model)) {
            return [
                'success' => false,
                'error' => 'Both manufacturer and model are required for specification search'
            ];
        }

        try {
            // Search for product pages
            $searchQuery = "{$manufacturer} {$model} specifications";
            
            $searchResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post("{$this->baseUrl}/search", [
                'query' => $searchQuery,
                'limit' => 3,
            ]);

            if (!$searchResponse->successful()) {
                Log::error('Firecrawl search API request failed', [
                    'status' => $searchResponse->status(),
                    'response' => $searchResponse->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to search for specifications'
                ];
            }

            $searchData = $searchResponse->json();
            
            // Handle different response formats
            $results = [];
            if (isset($searchData['data']['web'])) {
                $results = $searchData['data']['web'];
            } elseif (isset($searchData['data']) && is_array($searchData['data'])) {
                $results = $searchData['data'];
            } elseif (isset($searchData['results'])) {
                $results = $searchData['results'];
            }

            if (empty($results)) {
                return [
                    'success' => false,
                    'error' => 'No results found for the specified manufacturer and model'
                ];
            }

            // Try to extract specifications from the first result
            $firstResult = $results[0] ?? null;
            $url = $firstResult['url'] ?? $firstResult['link'] ?? null;
            
            if ($url) {
                return $this->extractSpecifications($url);
            }

            return [
                'success' => false,
                'error' => 'No valid URLs found in search results'
            ];

        } catch (\Exception $e) {
            Log::error('Firecrawl service exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while searching for specifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract specifications from a URL using Firecrawl scrape API.
     *
     * @param string $url
     * @return array{success: bool, specifications?: array, error?: string}
     */
    public function extractSpecifications(string $url): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Firecrawl API key is not configured'
            ];
        }

        try {
            // Define JSON schema for extraction
            $schema = [
                'type' => 'object',
                'properties' => [
                    'voltage' => ['type' => 'string', 'description' => 'Operating voltage (e.g., "110V", "220V", "380V")'],
                    'power' => ['type' => 'string', 'description' => 'Power rating (e.g., "500W", "2.5kW")'],
                    'weight' => ['type' => 'string', 'description' => 'Weight (e.g., "50kg", "120 lbs")'],
                    'dimensions' => ['type' => 'string', 'description' => 'Dimensions (e.g., "100x50x200cm", "40x20x80 inches")'],
                    'current' => ['type' => 'string', 'description' => 'Current rating (e.g., "10A", "5 amps")'],
                    'frequency' => ['type' => 'string', 'description' => 'Frequency (e.g., "50Hz", "60Hz")'],
                    'capacity' => ['type' => 'string', 'description' => 'Capacity or rating'],
                    'material' => ['type' => 'string', 'description' => 'Material composition'],
                    'description' => ['type' => 'string', 'description' => 'Product description'],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post("{$this->baseUrl}/scrape", [
                'url' => $url,
                'formats' => [
                    [
                        'type' => 'json',
                        'schema' => $schema,
                    ]
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Firecrawl scrape API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to extract specifications from URL'
                ];
            }

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                return [
                    'success' => false,
                    'error' => 'Failed to extract specifications'
                ];
            }

            $extractedJson = $data['data']['json'] ?? [];
            
            // Clean up the extracted data
            $specifications = [];
            foreach ($extractedJson as $key => $value) {
                if (!empty($value) && is_string($value)) {
                    $specifications[$key] = trim($value);
                }
            }

            if (empty($specifications)) {
                return [
                    'success' => false,
                    'error' => 'No specifications found on the page'
                ];
            }

            return [
                'success' => true,
                'specifications' => $specifications,
            ];

        } catch (\Exception $e) {
            Log::error('Firecrawl extract exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while extracting specifications: ' . $e->getMessage()
            ];
        }
    }
}

