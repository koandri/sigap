<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class OpenRouterService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', '');
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->model = config('services.openrouter.model', 'google/gemini-2.0-flash-exp:free');
    }

    /**
     * Analyze multiple asset images and extract information using AI.
     *
     * @param array<string> $imagesBase64 Array of base64 encoded images (with or without data URI prefix)
     * @return array{success: bool, suggested_name?: string, suggested_category?: string, manufacturer?: string|null, model?: string|null, serial_number?: string|null, confidence?: float, error?: string}
     */
    public function analyzeAssetImages(array $imagesBase64): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'OpenRouter API key is not configured'
            ];
        }

        if (empty($imagesBase64)) {
            return [
                'success' => false,
                'error' => 'No images provided for analysis'
            ];
        }

        try {
            // Prepare image content for API
            $imageContents = [];
            foreach ($imagesBase64 as $index => $imageBase64) {
                // Remove data URI prefix if present
                if (str_starts_with($imageBase64, 'data:image')) {
                    $imageBase64 = explode(',', $imageBase64)[1];
                }

                // Log image size for debugging
                $imageSize = strlen($imageBase64);
            Log::info('Preparing image for analysis', [
                'index' => $index,
                'size_bytes' => $imageSize,
                'size_kb' => round($imageSize / 1024, 2),
            ]);

                $imageContents[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:image/jpeg;base64,' . $imageBase64
                    ]
                ];
            }

            // Get existing asset categories for matching
            $categories = \App\Models\AssetCategory::active()->pluck('name')->toArray();
            $categoriesList = implode(', ', $categories);

            // Build prompt - request JSON format explicitly (as per OpenRouter docs)
            $prompt = "Analyze these " . count($imagesBase64) . " image(s) of an asset/equipment. ";
            $prompt .= "Look across all images for: name plates, model numbers, serial numbers, specifications labels, brand logos, and any identifying information. ";
            $prompt .= "Extract the following information and return ONLY valid JSON (no markdown, no code blocks, just the JSON object):\n\n";
            $prompt .= "1. suggested_name: A concise, descriptive name for this asset (maximum 255 characters). Focus on the type of equipment, its primary function, and any distinguishing features.\n";
            $prompt .= "2. suggested_category: Match against these existing categories: {$categoriesList}. Return the closest matching category name, or null if no match.\n";
            $prompt .= "3. manufacturer: The manufacturer/brand name if visible in any photo, or null.\n";
            $prompt .= "4. model: The model number if visible in any photo, or null.\n";
            $prompt .= "5. serial_number: The serial number if visible in any photo, or null.\n";
            $prompt .= "6. confidence: A confidence score between 0 and 1.\n\n";
            $prompt .= "Return ONLY the JSON object in this exact format (no other text, no markdown):\n";
            $prompt .= '{"suggested_name": "string", "suggested_category": "string or null", "manufacturer": "string or null", "model": "string or null", "serial_number": "string or null", "confidence": 0.0-1.0}';

            // Build messages array - text prompt first, then images (as per OpenRouter docs)
            $content = [
                [
                    'type' => 'text',
                    'text' => $prompt
                ]
            ];
            
            // Add images after text (recommended by OpenRouter)
            foreach ($imageContents as $imageContent) {
                $content[] = $imageContent;
            }
            
            $messages = [
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ];

            // Build request payload
            // Note: response_format may not be supported by all models, so we'll try without it first
            $requestPayload = [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.3,
            ];
            
            // Only add response_format if the model supports it (Gemini models may not)
            // We'll request JSON in the prompt instead

            $payloadSize = strlen(json_encode($requestPayload));
            Log::info('Sending image analysis request to OpenRouter', [
                'model' => $this->model,
                'image_count' => count($imageContents),
                'payload_size_bytes' => $payloadSize,
                'payload_size_kb' => round($payloadSize / 1024, 2),
                'payload_size_mb' => round($payloadSize / (1024 * 1024), 2),
                'api_key_length' => strlen($this->apiKey),
            ]);
            
            // Check if payload is too large (OpenRouter has limits)
            if ($payloadSize > 20 * 1024 * 1024) { // 20MB limit
                return [
                    'success' => false,
                    'error' => 'Image payload is too large (' . round($payloadSize / (1024 * 1024), 2) . 'MB). Please use fewer or smaller images.'
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'SIGAP Asset Management',
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", $requestPayload);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $errorData = $response->json();
                
                Log::error('OpenRouter API request failed', [
                    'status' => $response->status(),
                    'response' => $errorBody,
                    'error_data' => $errorData,
                    'model' => $this->model,
                ]);

                $errorMessage = 'Failed to analyze images. ';
                if (isset($errorData['error']['message'])) {
                    $errorMessage .= $errorData['error']['message'];
                } elseif (isset($errorData['error'])) {
                    if (is_string($errorData['error'])) {
                        $errorMessage .= $errorData['error'];
                    } else {
                        $errorMessage .= json_encode($errorData['error']);
                    }
                } elseif ($response->status() === 401) {
                    $errorMessage .= 'Invalid API key. Please check your OPENROUTER_API_KEY in .env file.';
                } elseif ($response->status() === 429) {
                    $errorMessage .= 'Rate limit exceeded. Please try again later.';
                } else {
                    $errorMessage .= 'HTTP ' . $response->status() . ': ' . $response->statusText();
                }

                return [
                    'success' => false,
                    'error' => $errorMessage
                ];
            }

            $data = $response->json();
            
            if (!isset($data['choices']) || empty($data['choices'])) {
                Log::error('OpenRouter API returned invalid response structure', [
                    'response' => $data,
                ]);
                return [
                    'success' => false,
                    'error' => 'Invalid response from AI service. Please try again.'
                ];
            }
            
            $content = trim($data['choices'][0]['message']['content'] ?? '');

            if (empty($content)) {
                Log::error('OpenRouter API returned empty content', [
                    'response' => $data,
                ]);
                return [
                    'success' => false,
                    'error' => 'Could not generate analysis from images'
                ];
            }

            // Parse JSON response
            $analysis = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try to extract JSON from markdown code blocks
                if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                    $analysis = json_decode($matches[1], true);
                } elseif (preg_match('/```\s*(.*?)\s*```/s', $content, $matches)) {
                    $analysis = json_decode($matches[1], true);
                }

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('OpenRouter JSON parse error', [
                        'content' => $content,
                        'error' => json_last_error_msg(),
                    ]);

                    return [
                        'success' => false,
                        'error' => 'Failed to parse AI response'
                    ];
                }
            }

            // Validate and clean up the response
            $result = [
                'success' => true,
                'suggested_name' => substr(trim($analysis['suggested_name'] ?? ''), 0, 255),
                'suggested_category' => $analysis['suggested_category'] ?? null,
                'manufacturer' => $analysis['manufacturer'] ?? null,
                'model' => $analysis['model'] ?? null,
                'serial_number' => $analysis['serial_number'] ?? null,
                'confidence' => min(max((float)($analysis['confidence'] ?? 0.5), 0.0), 1.0),
            ];

            // Clean up null strings
            foreach (['suggested_category', 'manufacturer', 'model', 'serial_number'] as $key) {
                if ($result[$key] === '' || $result[$key] === 'null') {
                    $result[$key] = null;
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('OpenRouter service exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while analyzing the images. Please check the logs for details.'
            ];
        }
    }

    /**
     * Test the OpenRouter API connection with a simple message.
     *
     * @return array{success: bool, message?: string, error?: string}
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'OpenRouter API key is not configured'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'SIGAP Asset Management',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Hello, respond with "OK" if you can read this.'
                    ]
                ],
                'max_tokens' => 10,
            ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $errorData = $response->json();
                
                Log::error('OpenRouter test connection failed', [
                    'status' => $response->status(),
                    'response' => $errorBody,
                    'error_data' => $errorData,
                ]);

                $errorMessage = 'API connection failed. ';
                if (isset($errorData['error']['message'])) {
                    $errorMessage .= $errorData['error']['message'];
                } elseif ($response->status() === 401) {
                    $errorMessage .= 'Invalid API key.';
                } else {
                    $errorMessage .= 'HTTP ' . $response->status() . ': ' . $response->statusText();
                }

                return [
                    'success' => false,
                    'error' => $errorMessage
                ];
            }

            $data = $response->json();
            $content = trim($data['choices'][0]['message']['content'] ?? '');

            return [
                'success' => true,
                'message' => $content ?: 'Connection successful but no response content'
            ];

        } catch (\Exception $e) {
            Log::error('OpenRouter test connection exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
}

