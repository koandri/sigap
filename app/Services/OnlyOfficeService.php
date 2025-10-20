<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class OnlyOfficeService
{
    private string $documentServerUrl;
    private string $callbackUrl;

    public function __construct()
    {
        $this->documentServerUrl = config('dms.onlyoffice.server_url', 'https://office.suryagroup.app');
        $this->callbackUrl = config('dms.onlyoffice.callback_url', route('document-versions.onlyoffice-callback'));
    }

    public function createDocument(DocumentVersion $version, string $fileType = 'docx'): string
    {
        $filename = $this->generateFilename($version, $fileType);
        $filePath = 'documents/onlyoffice/' . $filename;
        
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
        
        return [
            'document' => [
                'fileType' => $version->file_type,
                'key' => $this->generateDocumentKey($version),
                'title' => $version->document->title,
                'url' => $documentUrl,
            ],
            'documentType' => $this->getDocumentType($version->file_type),
            'editorConfig' => [
                'mode' => $this->getEditorMode($version),
                'lang' => 'en',
                'callbackUrl' => $callbackUrl,
                'user' => [
                    'id' => auth()->id(),
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
        // This would typically involve calling OnlyOffice API to convert document to PDF
        // For now, we'll return the original file path
        // In production, you'd implement the actual conversion logic
        
        $pdfPath = $this->generatePdfPath($version);
        
        // TODO: Implement actual PDF conversion via OnlyOffice API
        // For now, just copy the original file
        Storage::disk('s3')->copy($version->file_path, $pdfPath);
        
        return $pdfPath;
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
        $versionNumber = str_replace('.', '_', $version->version_number);
        
        return sprintf(
            '%s_v%s_%s.%s',
            $documentTitle,
            $versionNumber,
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
        // Return empty DOCX content
        // In production, you'd create a proper empty DOCX file
        return '';
    }

    private function getEmptyXlsxContent(): string
    {
        // Return empty XLSX content
        // In production, you'd create a proper empty XLSX file
        return '';
    }

    private function getEmptyPptxContent(): string
    {
        // Return empty PPTX content
        // In production, you'd create a proper empty PPTX file
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
}
