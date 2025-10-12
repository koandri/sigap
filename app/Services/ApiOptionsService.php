<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ApiOptionsService
{
    private const CACHE_PREFIX = 'api_options_';
    private const DEFAULT_CACHE_TTL = 300; // 5 minutes

    /**
     * Fetch options from external API
     */
    public function fetchOptions(array $config): array
    {
        $cacheKey = $this->generateCacheKey($config);
        
        // Try to get from cache first
        $cachedOptions = Cache::get($cacheKey);
        if ($cachedOptions !== null) {
            return $cachedOptions;
        }

        try {
            $options = $this->makeApiRequest($config);
            
            // Cache the results
            $cacheTtl = $config['cache_ttl'] ?? self::DEFAULT_CACHE_TTL;
            Cache::put($cacheKey, $options, $cacheTtl);
            
            return $options;
        } catch (\Exception $e) {
            Log::error('API Options Service Error', [
                'config' => $config,
                'error' => $e->getMessage()
            ]);
            
            // Return empty array on error
            return [];
        }
    }

    /**
     * Make the actual API request
     */
    private function makeApiRequest(array $config): array
    {
        $url = $config['url'];
        $method = strtoupper($config['method'] ?? 'GET');
        $headers = $config['headers'] ?? [];
        $params = $config['params'] ?? [];
        $timeout = $config['timeout'] ?? 30;

        // Add authentication if configured
        if (isset($config['auth'])) {
            $headers = array_merge($headers, $this->buildAuthHeaders($config['auth']));
        }

        $request = Http::timeout($timeout)->withHeaders($headers);

        // Make the request based on method
        $response = match ($method) {
            'POST' => $request->post($url, $params),
            'PUT' => $request->put($url, $params),
            'PATCH' => $request->patch($url, $params),
            'DELETE' => $request->delete($url, $params),
            default => $request->get($url, $params)
        };

        if (!$response->successful()) {
            throw new \Exception("API request failed with status: {$response->status()}");
        }

        return $this->parseResponse($response, $config);
    }

    /**
     * Parse API response based on configuration
     */
    private function parseResponse(Response $response, array $config): array
    {
        $data = $response->json();
        
        if (!$data) {
            throw new \Exception('Invalid JSON response from API');
        }

        $options = [];
        $valueField = $config['value_field'] ?? 'id';
        $labelField = $config['label_field'] ?? 'name';
        $dataPath = $config['data_path'] ?? null;

        // Navigate to the data if data_path is specified
        if ($dataPath) {
            $data = $this->getNestedValue($data, $dataPath);
        }

        // Ensure data is an array
        if (!is_array($data)) {
            throw new \Exception('Data path does not point to an array');
        }

        foreach ($data as $item) {
            if (is_array($item) && isset($item[$valueField])) {
                $options[] = [
                    'value' => (string) $item[$valueField],
                    'label' => $this->buildCombinedLabel($item, $labelField)
                ];
            }
        }

        return $options;
    }

    /**
     * Build combined label from template and data
     */
    private function buildCombinedLabel(array $item, string $labelTemplate): string
    {
        // If the label template contains curly braces, treat it as a template
        if (strpos($labelTemplate, '{') !== false && strpos($labelTemplate, '}') !== false) {
            $label = $labelTemplate;
            
            // Replace {field_name} with actual values
            preg_match_all('/\{([^}]+)\}/', $labelTemplate, $matches);
            foreach ($matches[1] as $fieldName) {
                $fieldValue = $item[$fieldName] ?? '';
                $label = str_replace('{' . $fieldName . '}', (string) $fieldValue, $label);
            }
            
            return $label;
        }
        
        // Simple field name - return the value directly
        return (string) ($item[$labelTemplate] ?? '');
    }

    /**
     * Get nested value from array using dot notation
     */
    private function getNestedValue(array $data, string $path)
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                throw new \Exception("Path '{$path}' not found in response data");
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Build authentication headers
     */
    private function buildAuthHeaders(array $auth): array
    {
        return match ($auth['type']) {
            'bearer' => ['Authorization' => 'Bearer ' . $auth['token']],
            'basic' => ['Authorization' => 'Basic ' . base64_encode($auth['username'] . ':' . $auth['password'])],
            'api_key' => [$auth['header_name'] ?? 'X-API-Key' => $auth['api_key']],
            default => []
        };
    }

    /**
     * Generate cache key for configuration
     */
    private function generateCacheKey(array $config): string
    {
        $keyData = [
            'url' => $config['url'],
            'method' => $config['method'] ?? 'GET',
            'params' => $config['params'] ?? [],
            'value_field' => $config['value_field'] ?? 'id',
            'label_field' => $config['label_field'] ?? 'name',
            'data_path' => $config['data_path'] ?? null
        ];

        return self::CACHE_PREFIX . md5(serialize($keyData));
    }

    /**
     * Clear cache for specific configuration
     */
    public function clearCache(array $config): void
    {
        $cacheKey = $this->generateCacheKey($config);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all API options cache
     */
    public function clearAllCache(): void
    {
        // This would require a more sophisticated cache key management
        // For now, we'll rely on TTL expiration
    }

    /**
     * Validate API configuration
     */
    public function validateConfig(array $config): array
    {
        $errors = [];

        if (empty($config['url'])) {
            $errors[] = 'URL is required';
        } elseif (!filter_var($config['url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'URL must be a valid URL';
        }

        if (isset($config['method']) && !in_array(strtoupper($config['method']), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            $errors[] = 'Method must be one of: GET, POST, PUT, PATCH, DELETE';
        }

        if (empty($config['value_field'])) {
            $errors[] = 'Value field is required';
        }

        if (empty($config['label_field'])) {
            $errors[] = 'Label field is required';
        }

        if (isset($config['auth'])) {
            $authErrors = $this->validateAuthConfig($config['auth']);
            $errors = array_merge($errors, $authErrors);
        }

        return $errors;
    }

    /**
     * Validate authentication configuration
     */
    private function validateAuthConfig(array $auth): array
    {
        $errors = [];

        if (empty($auth['type'])) {
            $errors[] = 'Auth type is required';
        } elseif (!in_array($auth['type'], ['bearer', 'basic', 'api_key'])) {
            $errors[] = 'Auth type must be one of: bearer, basic, api_key';
        }

        switch ($auth['type']) {
            case 'bearer':
                if (empty($auth['token'])) {
                    $errors[] = 'Token is required for bearer authentication';
                }
                break;
            case 'basic':
                if (empty($auth['username']) || empty($auth['password'])) {
                    $errors[] = 'Username and password are required for basic authentication';
                }
                break;
            case 'api_key':
                if (empty($auth['api_key'])) {
                    $errors[] = 'API key is required for API key authentication';
                }
                break;
        }

        return $errors;
    }
}

