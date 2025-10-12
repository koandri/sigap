<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormVersion;
use App\Services\ApiOptionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FormFieldOptionsController extends Controller
{
    public function __construct(
        private readonly ApiOptionsService $apiOptionsService
    ) {}

    /**
     * Get options for a specific form field
     */
    public function getOptions(Request $request, Form $form, FormVersion $version, FormField $field): JsonResponse
    {
        // Verify relationships
        if ($version->form_id !== $form->id || $field->form_version_id !== $version->id) {
            return response()->json(['error' => 'Field not found'], 404);
        }

        // Check if field type supports options
        if (!$field->hasOptions()) {
            return response()->json(['error' => 'Field type does not support options'], 400);
        }

        try {
            $options = [];

            // Add static options
            foreach ($field->options as $option) {
                $options[] = [
                    'value' => $option->option_value,
                    'label' => $option->option_label
                ];
            }

            // Add API options if configured
            if ($field->hasApiSource()) {
                $apiOptions = $this->apiOptionsService->fetchOptions($field->getApiSourceConfig());
                $options = array_merge($options, $apiOptions);
            }

            return response()->json([
                'success' => true,
                'options' => $options,
                'field_code' => $field->field_code,
                'field_type' => $field->field_type,
                'has_api_source' => $field->hasApiSource()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch options',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test API configuration
     */
    public function testApiConfig(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'config' => 'required|array',
                'config.url' => 'required|url',
                'config.method' => 'nullable|in:GET,POST,PUT,PATCH,DELETE',
                'config.value_field' => 'required|string',
                'config.label_field' => 'required|string',
                'config.data_path' => 'nullable|string',
                'config.auth' => 'nullable|array',
                'config.params' => 'nullable|array',
                'config.headers' => 'nullable|array',
                'config.timeout' => 'nullable|integer|min:1|max:300',
                'config.cache_ttl' => 'nullable|integer|min:60|max:3600'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $config = $request->input('config');

        // Validate configuration
        $errors = $this->apiOptionsService->validateConfig($config);
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'errors' => $errors
            ], 400);
        }

        try {
            $options = $this->apiOptionsService->fetchOptions($config);
            
            return response()->json([
                'success' => true,
                'options' => $options,
                'count' => count($options),
                'message' => 'API configuration test successful'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'API test failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache for a specific field
     */
    public function clearCache(Request $request, Form $form, FormVersion $version, FormField $field): JsonResponse
    {
        // Verify relationships
        if ($version->form_id !== $form->id || $field->form_version_id !== $version->id) {
            return response()->json(['error' => 'Field not found'], 404);
        }

        if (!$field->hasApiSource()) {
            return response()->json(['error' => 'Field does not use API source'], 400);
        }

        try {
            $this->apiOptionsService->clearCache($field->getApiSourceConfig());
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cache',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}