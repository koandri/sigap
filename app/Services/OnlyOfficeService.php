<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

final class OnlyOfficeService
{
    private string $documentServerUrl;
    private string $callbackUrl;

    public function __construct()
    {
        $this->documentServerUrl = config('dms.onlyoffice.server_url', 'https://office.suryagroup.app');
        $this->callbackUrl = config('dms.onlyoffice.callback_url');
    }

    public function createDocument(DocumentVersion $version, string $fileType = 'docx'): string
    {
        $filename = $this->generateFilename($version, $fileType);
        $filePath = 'documents/versions/' . $version->document_id . '/' . $filename;
        
        // Create empty document file
        $this->createEmptyDocument($filePath, $fileType);
        
        // Update version with file path
        $version->update(['file_path' => $filePath]);
        
        return $filePath;
    }

    public function getEditorConfig(DocumentVersion $version): array
    {
        $documentUrl = Storage::disk('s3')->url($version->file_path);
        $callbackUrl = route('document-versions.onlyoffice-callback', $version);
        
        $config = [
            'document' => [
                'fileType' => $version->file_type,
                'key' => $this->generateDocumentKey($version),
                'title' => $version->document->title,
                'url' => $documentUrl,
                'permissions' => [
                    'download' => false,
                    'print' => false,
                    'edit' => $this->getEditorMode($version) === 'edit',
                    'review' => false,
                    'comment' => false,
                ],
            ],
            'documentType' => $this->getDocumentType($version->file_type),
            'editorConfig' => [
                'mode' => $this->getEditorMode($version),
                'lang' => 'en',
                'callbackUrl' => $callbackUrl,
                'user' => [
                    'id' => (string) auth()->id(),
                    'name' => auth()->user()->name,
                ],
                'customization' => [
                    'autosave' => true,
                    'forcesave' => true,
                ],
            ],
            'height' => '100%',
            'width' => '100%',
        ];
        
        // Add JWT token if enabled
        if (config('dms.onlyoffice.jwt_enabled') && config('dms.onlyoffice.secret')) {
            $config['token'] = $this->generateJWT($config);
        }
        
        // Log configuration for debugging
        \Log::info('OnlyOffice Editor Config', [
            'document_url' => $documentUrl,
            'callback_url' => $callbackUrl,
            'file_path' => $version->file_path,
            'file_type' => $version->file_type,
            'jwt_enabled' => config('dms.onlyoffice.jwt_enabled'),
            'has_secret' => !empty(config('dms.onlyoffice.secret')),
        ]);
        
        return $config;
    }

    public function handleCallback(DocumentVersion $version, array $callbackData): void
    {
        if (isset($callbackData['status'])) {
            switch ($callbackData['status']) {
                case 0: // No document with the key identifier could be found
                    $this->handleDocumentNotFound($version);
                    break;
                case 1: // Document is being edited
                    $this->handleDocumentBeingEdited($version, $callbackData);
                    break;
                case 2: // Document is ready for saving
                    $this->handleDocumentReadyForSaving($version, $callbackData);
                    break;
                case 3: // Document saving error has occurred
                    $this->handleDocumentSavingError($version, $callbackData);
                    break;
                case 4: // Document is closed with no changes
                    $this->handleDocumentClosed($version, $callbackData);
                    break;
                case 6: // Document is being edited, but the current document state is saved
                    $this->handleDocumentSaved($version, $callbackData);
                    break;
                case 7: // Error has occurred while force saving the document
                    $this->handleForceSaveError($version, $callbackData);
                    break;
            }
        }
    }

    public function convertToPDF(DocumentVersion $version): string
    {
        // Use public URL since S3 files are publicly accessible
        $documentUrl = Storage::disk('s3')->url($version->file_path);
        $pdfPath = $this->generatePdfPath($version);
        
        // Try different converter endpoint paths
        $baseUrl = str_replace('/onlyoffice', '', rtrim($this->documentServerUrl, '/'));
        $possibleEndpoints = [
            // Nextcloud Office/OnlyOffice conversion endpoint (most likely for Nextcloud integration)
            $baseUrl . '/ocs/v2.php/apps/richdocuments/api/v1/documents/conversion',
            // Alternative Nextcloud endpoint format
            $baseUrl . '/index.php/apps/richdocuments/api/v1/documents/conversion',
            // Standard Document Server endpoint (if using standalone Document Server)
            $baseUrl . '/ConvertService.ashx',
            // Alternative Document Server endpoint
            rtrim($this->documentServerUrl, '/') . '/ConvertService.ashx',
        ];
        
        // Determine if this is a Nextcloud endpoint
        $isNextcloudEndpoint = function($url) {
            return str_contains($url, '/ocs/') || str_contains($url, '/index.php/apps/richdocuments/');
        };
        
        $lastError = null;
        
        // Try each endpoint
        foreach ($possibleEndpoints as $converterUrl) {
            try {
                $isNextcloud = $isNextcloudEndpoint($converterUrl);
                
                // Prepare request data based on endpoint type
                if ($isNextcloud) {
                    // Nextcloud Office API format
                    $requestData = [
                        'fileId' => $version->id,
                        'fileType' => $version->file_type,
                        'outputFormat' => 'pdf',
                        'documentUrl' => $documentUrl,
                    ];
                } else {
                    // Standard OnlyOffice Document Server format
                    $requestData = [
                        'async' => false,
                        'filetype' => $version->file_type,
                        'key' => $this->generateDocumentKey($version) . '_conv',
                        'outputtype' => 'pdf',
                        'title' => $version->document->title,
                        'url' => $documentUrl,
                    ];
                    
                    // Add JWT token if enabled (for Document Server)
                    if (config('dms.onlyoffice.jwt_enabled') && config('dms.onlyoffice.secret')) {
                        $requestData['token'] = $this->generateJWT($requestData);
                    }
                }
                
                Log::info('Converting document to PDF via OnlyOffice', [
                    'version_id' => $version->id,
                    'converter_url' => $converterUrl,
                    'document_url' => $documentUrl,
                    'file_type' => $version->file_type,
                    'is_nextcloud' => $isNextcloud,
                ]);
                
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ];
                
                $response = Http::withHeaders($headers)->timeout(120)->post($converterUrl, $requestData);
                
                if (!$response->successful()) {
                    $errorBody = $response->body();
                    Log::warning('OnlyOffice conversion request failed', [
                        'version_id' => $version->id,
                        'converter_url' => $converterUrl,
                        'status' => $response->status(),
                        'response' => $errorBody,
                    ]);
                    $lastError = 'OnlyOffice conversion failed (HTTP ' . $response->status() . '): ' . $errorBody;
                    continue; // Try next endpoint
                }
                
                $responseData = $response->json();
                
                // Handle Nextcloud API response format (wrapped in ocs format)
                if ($isNextcloud && isset($responseData['ocs'])) {
                    $responseData = $responseData['ocs'];
                    if (isset($responseData['data'])) {
                        $responseData = $responseData['data'];
                    }
                }
                
                // Check for error in response
                if (isset($responseData['error'])) {
                    Log::warning('OnlyOffice conversion returned error', [
                        'version_id' => $version->id,
                        'converter_url' => $converterUrl,
                        'error' => $responseData['error'],
                    ]);
                    $lastError = 'OnlyOffice conversion error: ' . $responseData['error'];
                    continue; // Try next endpoint
                }
                
                // Nextcloud might return different field names
                $pdfUrl = $responseData['fileUrl'] ?? $responseData['url'] ?? $responseData['downloadUrl'] ?? null;
                
                if (!$pdfUrl) {
                    Log::warning('OnlyOffice conversion did not return fileUrl', [
                        'version_id' => $version->id,
                        'converter_url' => $converterUrl,
                        'response' => $responseData,
                    ]);
                    $lastError = 'OnlyOffice conversion did not return fileUrl. Response: ' . json_encode($responseData);
                    continue; // Try next endpoint
                }
                
                // For Nextcloud, the URL might be relative - make it absolute
                if ($isNextcloud && !str_starts_with($pdfUrl, 'http')) {
                    $baseUrl = parse_url($converterUrl, PHP_URL_SCHEME) . '://' . parse_url($converterUrl, PHP_URL_HOST);
                    if (parse_url($converterUrl, PHP_URL_PORT)) {
                        $baseUrl .= ':' . parse_url($converterUrl, PHP_URL_PORT);
                    }
                    $pdfUrl = $baseUrl . $pdfUrl;
                }
                
                // Download the converted PDF
                Log::info('Downloading converted PDF from OnlyOffice', [
                    'version_id' => $version->id,
                    'pdf_url' => $pdfUrl,
                ]);
                
                $pdfResponse = Http::timeout(120)->get($pdfUrl);
                
                if (!$pdfResponse->successful()) {
                    throw new \Exception('Failed to download converted PDF: HTTP ' . $pdfResponse->status());
                }
                
                $pdfContent = $pdfResponse->body();
                
                // Verify it's actually PDF content
                if (substr($pdfContent, 0, 4) !== '%PDF') {
                    throw new \Exception('Downloaded content is not a valid PDF file');
                }
                
                // Save the PDF to storage
                Storage::disk('s3')->put($pdfPath, $pdfContent, 'public');
                
                Log::info('PDF conversion successful via OnlyOffice', [
                    'version_id' => $version->id,
                    'pdf_path' => $pdfPath,
                    'converter_url' => $converterUrl,
                    'pdf_size' => strlen($pdfContent),
                ]);
                
                return $pdfPath;
            } catch (\Exception $e) {
                Log::warning('OnlyOffice conversion attempt failed', [
                    'version_id' => $version->id,
                    'converter_url' => $converterUrl,
                    'error' => $e->getMessage(),
                ]);
                $lastError = $e->getMessage();
                continue; // Try next endpoint
            }
        }
        
        // All endpoints failed - log and throw exception (don't fallback to PhpWord)
        Log::error('All OnlyOffice PDF conversion endpoints failed', [
            'version_id' => $version->id,
            'document_url' => $documentUrl,
            'last_error' => $lastError,
            'attempted_endpoints' => $possibleEndpoints,
        ]);
        
        throw new \Exception('PDF conversion failed: ' . ($lastError ?? 'All conversion endpoints failed. Please check OnlyOffice server configuration.'));
    }
    

    public function getDocumentUrl(DocumentVersion $version): string
    {
        return Storage::disk('s3')->url($version->file_path);
    }

    public function generateSignedUrl(DocumentVersion $version, int $expiresInMinutes = 60): string
    {
        return Storage::disk('s3')->temporaryUrl(
            $version->file_path,
            now()->addMinutes($expiresInMinutes)
        );
    }

    private function generateFilename(DocumentVersion $version, string $fileType): string
    {
        $documentTitle = Str::slug($version->document->title);
        
        return sprintf(
            '%s_v%s_%s.%s',
            $documentTitle,
            $version->version_number,
            time(),
            $fileType
        );
    }

    private function createEmptyDocument(string $filePath, string $fileType): void
    {
        $content = $this->getEmptyDocumentContent($fileType);
        Storage::disk('s3')->put($filePath, $content, 'public');
    }

    private function getEmptyDocumentContent(string $fileType): string
    {
        return match ($fileType) {
            'docx' => $this->getEmptyDocxContent(),
            'xlsx' => $this->getEmptyXlsxContent(),
            'pptx' => $this->getEmptyPptxContent(),
            default => $this->getEmptyDocxContent(),
        };
    }

    private function getEmptyDocxContent(): string
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('');
        
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        
        return $content;
    }

    private function getEmptyXlsxContent(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '');
        
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        
        return $content;
    }

    private function getEmptyPptxContent(): string
    {
        // For now, return empty string. PPTX support can be added later if needed.
        return '';
    }

    private function generateDocumentKey(DocumentVersion $version): string
    {
        return $version->id . '_' . time();
    }

    private function getDocumentType(string $fileType): string
    {
        return match ($fileType) {
            'docx' => 'word',
            'xlsx' => 'cell',
            'pptx' => 'slide',
            default => 'word',
        };
    }

    private function getEditorMode(DocumentVersion $version): string
    {
        // Check if user can edit the version
        if ($version->isDraft() && $version->created_by === auth()->id()) {
            return 'edit';
        }
        
        return 'view';
    }

    private function generatePdfPath(DocumentVersion $version): string
    {
        $pathInfo = pathinfo($version->file_path);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.pdf';
    }

    private function handleDocumentNotFound(DocumentVersion $version): void
    {
        // Log error and potentially recreate document
        \Log::error("OnlyOffice document not found for version {$version->id}");
    }

    private function handleDocumentBeingEdited(DocumentVersion $version, array $callbackData): void
    {
        // Document is being edited, no action needed
    }

    private function handleDocumentReadyForSaving(DocumentVersion $version, array $callbackData): void
    {
        // Download and save the updated document
        if (isset($callbackData['url'])) {
            $this->downloadAndSaveDocument($version, $callbackData['url']);
        }
    }

    private function handleDocumentSavingError(DocumentVersion $version, array $callbackData): void
    {
        \Log::error("OnlyOffice document saving error for version {$version->id}", $callbackData);
    }

    private function handleDocumentClosed(DocumentVersion $version, array $callbackData): void
    {
        // Document was closed without changes
    }

    private function handleDocumentSaved(DocumentVersion $version, array $callbackData): void
    {
        // Document was saved successfully
        if (isset($callbackData['url'])) {
            $this->downloadAndSaveDocument($version, $callbackData['url']);
        }
    }

    private function handleForceSaveError(DocumentVersion $version, array $callbackData): void
    {
        \Log::error("OnlyOffice force save error for version {$version->id}", $callbackData);
    }

    private function downloadAndSaveDocument(DocumentVersion $version, string $url): void
    {
        try {
            $content = file_get_contents($url);
            Storage::disk('s3')->put($version->file_path, $content, 'public');
        } catch (\Exception $e) {
            \Log::error("Failed to download document for version {$version->id}: " . $e->getMessage());
        }
    }

    private function generateJWT(array $payload): string
    {
        $secret = config('dms.onlyoffice.secret');
        
        if (empty($secret)) {
            throw new \Exception('OnlyOffice JWT secret is not configured');
        }
        
        // JWT Header
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        // Encode header and payload
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        // Create signature
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        // Return JWT token
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
