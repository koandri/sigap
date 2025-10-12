<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Typography\FontFactory;

use App\Models\FormAnswer;

class FileController extends Controller
{
    private ImageManager $imageManager;
    
    public function __construct()
    {
        // Initialize ImageManager with driver
        $driver = config('image.driver', 'gd') === 'imagick' 
            ? new ImagickDriver() 
            : new GdDriver();
            
        $this->imageManager = new ImageManager($driver);
    }

    /**
     * Preview file (for PDFs and images)
     */
    public function preview(Request $request, $answerId, $fileIndex = null)
    {
        $answer = FormAnswer::findOrFail($answerId);
        
        // Check permission
        if (!$this->userCanAccessFile($answer)) {
            abort(403, 'Unauthorized access to file');
        }
        
        // Get file path
        $filePath = $this->getFilePath($answer, $fileIndex);
        $fileMetadata = $this->getFileMetadata($answer, $fileIndex);
        
        if (!$filePath || !Storage::disk('sigap')->exists($filePath)) {
            abort(404, 'File not found');
        }
        
        $mimeType = $fileMetadata['mime_type'] ?? Storage::disk('sigap')->mimeType($filePath);
        
        // Determine if file can be previewed
        $previewableTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml'
        ];
        
        if (in_array($mimeType, $previewableTypes)) {
            // For images, add watermark
            if (str_starts_with($mimeType, 'image/')) {
                return $this->getWatermarkedImage($filePath, $fileMetadata, 'preview');
            }

            return Storage::disk('sigap')->response($filePath, null, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . ($fileMetadata['original_name'] ?? basename($filePath)) . '"',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Cross-Origin-Embedder-Policy' => 'unsafe-none',
                'Cross-Origin-Opener-Policy' => 'same-origin',
                'Cross-Origin-Resource-Policy' => 'same-origin'
            ]);
        } else {
            // Force download for non-previewable files
            return $this->download($request, $answerId, $fileIndex);
        }
    }
    
    /**
     * Download file
     */
    public function download(Request $request, $answerId, $fileIndex = null)
    {
        $answer = FormAnswer::findOrFail($answerId);
        
        // Check permission
        if (!$this->userCanAccessFile($answer)) {
            abort(403, 'Unauthorized access to file');
        }
        
        // Get file path and metadata
        $filePath = $this->getFilePath($answer, $fileIndex);
        $fileMetadata = $this->getFileMetadata($answer, $fileIndex);
        
        if (!$filePath || !Storage::disk('sigap')->exists($filePath)) {
            abort(404, 'File not found');
        }
        
        $fileName = $fileMetadata['original_name'] ?? basename($filePath);
        $mimeType = $fileMetadata['mime_type'] ?? Storage::disk('sigap')->mimeType($filePath);
        
        // For images, add download watermark
        if (str_starts_with($mimeType, 'image/')) {
            return $this->downloadWatermarkedImage($filePath, $fileMetadata, $fileName);
        }
        
        return Storage::disk('sigap')->download($filePath, $fileName, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
    
    /**
     * Get thumbnail for image files
     */
    public function thumbnail(Request $request, $answerId, $fileIndex = null)
    {
        $answer = FormAnswer::findOrFail($answerId);
        
        // Check permission
        if (!$this->userCanAccessFile($answer)) {
            abort(403);
        }
        
        $filePath = $this->getFilePath($answer, $fileIndex);
        $fileMetadata = $this->getFileMetadata($answer, $fileIndex);
        
        if (!$filePath || !Storage::disk('sigap')->exists($filePath)) {
            abort(404);
        }
        
        $mimeType = $fileMetadata['mime_type'] ?? '';
        
        // Only process images
        if (!str_starts_with($mimeType, 'image/')) {
            abort(404);
        }

        return $this->getWatermarkedImage($filePath, $fileMetadata, 'thumbnail', [
            'width' => $request->get('w', 400),
            'height' => $request->get('h', 300),
            'quality' => $request->get('q', 85),
            'mode' => $request->get('mode', 'fit')
        ]);
    }

    /**
     * Generate watermarked image
     */
    private function getWatermarkedImage($filePath, $fileMetadata, $type = 'preview', $options = [])
    {
        try {
            // Read and process image
            $imageContent = Storage::disk('sigap')->get($filePath);
            $image = $this->imageManager->read($imageContent);
            
            // Resize for thumbnails
            if ($type === 'thumbnail' && isset($options['width']) && isset($options['height'])) {
                $maxWidth = $options['width'];
                $maxHeight = $options['height'];
                $mode = $options['mode'] ?? 'fit';

                // Get original dimensions
                $originalWidth = $image->width();
                $originalHeight = $image->height();
                $originalAspectRatio = $originalWidth / $originalHeight;

                switch ($mode) {
                    case 'fit':
                        // Preserve aspect ratio, fit within bounds
                        if ($originalAspectRatio > ($maxWidth / $maxHeight)) {
                            // Image is wider than target ratio, scale by width
                            $newWidth = $maxWidth;
                            $newHeight = (int) ($maxWidth / $originalAspectRatio);
                        } else {
                            // Image is taller than target ratio, scale by height
                            $newHeight = $maxHeight;
                            $newWidth = (int) ($maxHeight * $originalAspectRatio);
                        }
                        $image->resize($newWidth, $newHeight);
                        break;
                    case 'resize':
                        $image->scale($maxWidth, $maxHeight);
                        break;
                    case 'crop':
                        $image->cover($maxWidth, $maxHeight);
                        break;
                    case 'pad':
                        $image->pad($maxWidth, $maxHeight, '#ffffff');
                        break;
                    default:
                        // Default: preserve aspect ratio, fit within bounds
                        if ($originalAspectRatio > ($maxWidth / $maxHeight)) {
                            $newWidth = $maxWidth;
                            $newHeight = (int) ($maxWidth / $originalAspectRatio);
                        } else {
                            $newHeight = $maxHeight;
                            $newWidth = (int) ($maxHeight * $originalAspectRatio);
                        }
                        $image->resize($newWidth, $newHeight);
                }
            }
            
            // Add watermark
            $this->addWatermark($image, $type);
            
            // Encode to JPEG
            $quality = $options['quality'] ?? ($type === 'thumbnail' ? 85 : 95);
            $encoded = $image->toJpeg((int) $quality);
            
            // Return response with no-cache headers
            return response($encoded)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0')
                ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
                
        } catch (\Exception $e) {
            \Log::error('Watermarked image generation failed: ' . $e->getMessage());
            
            // Return original image if watermarking fails
            return Storage::disk('sigap')->response($filePath, null, [
                'Content-Type' => $fileMetadata['mime_type'] ?? 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }
    }
    
    /**
     * Add comprehensive watermark to image
     */
    private function addWatermark($image, $type = 'preview')
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        $settings = config('watermark.settings.' . $type, config('watermark.settings.preview'));
        
        // Add text watermark
        $this->addAdvancedTextWatermark($image, $settings, $imageWidth, $imageHeight);
        
        // Add logo watermark if available
        if ($type !== 'thumbnail') { // Skip logo for small thumbnails
            $this->addLogoWatermark($image, config('watermark.logo.position', 'bottom-right'));
        }
        
        // Add timestamp watermark for audit
        $this->addTimestampWatermark($image, $imageWidth, $imageHeight);
    }

    /**
     * Add advanced text watermark with background
     */
    private function addAdvancedTextWatermark($image, $settings, $imageWidth, $imageHeight)
    {
        $text = $settings['text'] ?? 'PT SIAP';
        $fontSize = max(12, $imageWidth / ($settings['size_ratio'] ?? 20));
        $opacity = $settings['opacity'] ?? 70;
        $position = $settings['position'] ?? 'center';
        $angle = $settings['angle'] ?? 0;
        $color = $settings['color'] ?? 'rgba(255, 255, 255, 0.8)';
        $bgColor = $settings['background_color'] ?? 'rgba(0, 0, 0, 0.5)';
        $fontPath = $settings['fontPath'];
        
        // Calculate position
        [$x, $y, $align] = $this->calculateWatermarkPosition($position, $imageWidth, $imageHeight, $fontSize);

        // Add background text for better readability (shadow effect)
        $image->text($text, $x + 2, $y + 2, function (FontFactory $font) use ($fontSize, $bgColor, $align, $angle, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color($bgColor);
            $font->align($align);
            $font->valign('middle');
            $font->angle($angle);
        });

        // Add main text
        $image->text($text, $x + 2, $y + 2, function (FontFactory $font) use ($fontSize, $color, $align, $angle, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color($color);
            $font->align($align);
            $font->valign('middle');
            $font->angle($angle);
        });
    }

    /**
     * Add logo watermark
     */
    private function addLogoWatermark($image, $position = 'bottom-right')
    {
        $logoConfig = config('watermark.logo');
        $logoPath = $logoConfig['path'] ?? public_path('images/watermarks/pt-siap-logo.png');
        
        if (!file_exists($logoPath)) {
            return; // Skip if logo doesn't exist
        }
        
        try {
            $logo = $this->imageManager->read($logoPath);
            
            // Resize logo proportionally
            $maxSize = min($image->width(), $image->height()) * ($logoConfig['max_size_percent'] / 100);
            
            if ($logo->width() > $maxSize || $logo->height() > $maxSize) {
                $logo->scale($maxSize, $maxSize);
            }
            
            // Calculate position
            [$x, $y] = $this->calculateLogoPosition($position, $image->width(), $image->height(), $logo->width(), $logo->height());
            
            // Apply logo with opacity
            $image->place($logo, 'top-left', $x, $y, $logoConfig['opacity'] ?? 70);
            
        } catch (\Exception $e) {
            \Log::warning('Logo watermark failed: ' . $e->getMessage());
        }
    }

    /**
     * Add timestamp watermark for audit trail
     */
    private function addTimestampWatermark($image, $imageWidth, $imageHeight)
    {
        $timestamp = auth()->user()->name . ' - ' . now()->format('d/m/Y H:i');
        $fontSize = max(8, $imageWidth / 60);
        $fontPath = public_path('fonts/Montserrat-VariableFont_wght.ttf');
        
        // Position at top-left corner
        $image->text($timestamp, 10, $fontSize + 10, function (FontFactory $font) use ($fontSize, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color('rgba(255, 255, 255, 0.8)');
            $font->align('left');
            $font->valign('top');
        });
        
        // Add background for timestamp
        $image->text($timestamp, 12, $fontSize + 12, function (FontFactory $font) use ($fontSize, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color('rgba(0, 0, 0, 0.6)');
            $font->align('left');
            $font->valign('top');
        });
    }

    /**
     * Calculate watermark text position
     */
    private function calculateWatermarkPosition($position, $width, $height, $fontSize)
    {
        switch ($position) {
            case 'top-left':
                return [20, $fontSize + 20, 'left'];
            case 'top-right':
                return [$width - 20, $fontSize + 20, 'right'];
            case 'bottom-left':
                return [20, $height - 20, 'left'];
            case 'bottom-right':
                return [$width - 20, $height - 20, 'right'];
            case 'center':
            default:
                return [$width / 2, $height / 2, 'center'];
        }
    }

    /**
     * Calculate logo position
     */
    private function calculateLogoPosition($position, $imageWidth, $imageHeight, $logoWidth, $logoHeight)
    {
        $margin = 20;
        
        switch ($position) {
            case 'top-left':
                return [$margin, $margin];
            case 'top-right':
                return [$imageWidth - $logoWidth - $margin, $margin];
            case 'bottom-left':
                return [$margin, $imageHeight - $logoHeight - $margin];
            case 'bottom-right':
            default:
                return [$imageWidth - $logoWidth - $margin, $imageHeight - $logoHeight - $margin];
        }
    }
    
    /**
     * Download original file without watermark
     */
    public function downloadOriginal(Request $request, $answerId, $fileIndex = null)
    {
        $answer = FormAnswer::findOrFail($answerId);
        
        // Check permission - only admins can download original
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) {
            abort(403, 'Only administrators can download original files');
        }
        
        $filePath = $this->getFilePath($answer, $fileIndex);
        $fileMetadata = $this->getFileMetadata($answer, $fileIndex);
        
        if (!$filePath || !Storage::disk('sigap')->exists($filePath)) {
            abort(404, 'File not found');
        }
        
        $fileName = 'ORIGINAL_' . ($fileMetadata['original_name'] ?? basename($filePath));
        
        return Storage::disk('sigap')->download($filePath, $fileName, [
            'Content-Type' => $fileMetadata['mime_type'] ?? 'application/octet-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ]);
    }

    /**
     * Preview file with watermark for images (optional)
     */
    public function previewWithWatermark(Request $request, $answerId, $fileIndex = null)
    {
        $answer = FormAnswer::findOrFail($answerId);
        
        // Check permission
        if (!$this->userCanAccessFile($answer)) {
            abort(403);
        }
        
        $filePath = $this->getFilePath($answer, $fileIndex);
        $fileMetadata = $this->getFileMetadata($answer, $fileIndex);
        
        if (!$filePath || !Storage::disk('sigap')->exists($filePath)) {
            abort(404);
        }
        
        $mimeType = $fileMetadata['mime_type'] ?? '';
        
        // Only process images
        if (!str_starts_with($mimeType, 'image/')) {
            return $this->preview($request, $answerId, $fileIndex);
        }
        
        try {
            // Read image
            $imageContent = Storage::disk('sigap')->get($filePath);
            $image = $this->imageManager->read($imageContent);
            
            // Add watermark text
            $image->text('PT SIAP - CONFIDENTIAL', 50, 50, function($font) {
                $font->file(public_path('fonts/Arial.ttf')); // Make sure font exists
                $font->size(24);
                $font->color('#ffffff');
                $font->align('left');
                $font->valign('top');
                $font->angle(45);
            });
            
            // Or add watermark image
            // $watermark = $this->imageManager->read(public_path('images/watermark.png'));
            // $image->place($watermark, 'bottom-right', 10, 10, 50); // 50% opacity
            
            // Return watermarked image
            return response($image->toJpeg(90))
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
                
        } catch (\Exception $e) {
            \Log::error('Watermark generation failed: ' . $e->getMessage());
            
            // Return original if watermarking fails
            return $this->preview($request, $answerId, $fileIndex);
        }
    }

    /**
     * Generate multiple thumbnail sizes at once
     */
    public function generateThumbnailSizes($filePath, $sizes = [])
    {
        try {
            $imageContent = Storage::disk('sigap')->get($filePath);
            $image = $this->imageManager->read($imageContent);
            
            $thumbnails = [];
            
            foreach ($sizes as $size) {
                $width = $size['width'] ?? 300;
                $height = $size['height'] ?? 300;
                $quality = $size['quality'] ?? 80;
                
                // Clone image for each size
                $resized = clone $image;
                $resized->cover($width, $height);
                
                $cacheKey = md5($filePath . $width . $height);
                $cachePath = 'thumbnails/' . $cacheKey . '.jpg';
                
                Storage::disk('sigap')->put(
                    $cachePath, 
                    $resized->toJpeg((int) $quality)
                );
                
                $thumbnails[] = [
                    'size' => "{$width}x{$height}",
                    'path' => $cachePath
                ];
            }
            
            return $thumbnails;
            
        } catch (\Exception $e) {
            \Log::error('Batch thumbnail generation failed: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Stream large files
     */
    public function stream(Request $request, $answerId, $fileIndex = null): StreamedResponse
    {
        $answer = FormAnswer::findOrFail($answerId);
        
        // Check permission
        if (!$this->userCanAccessFile($answer)) {
            abort(403);
        }
        
        $filePath = $this->getFilePath($answer, $fileIndex);
        $fileMetadata = $this->getFileMetadata($answer, $fileIndex);
        
        if (!$filePath || !Storage::disk('sigap')->exists($filePath)) {
            abort(404);
        }
        
        $fileName = $fileMetadata['original_name'] ?? basename($filePath);
        $mimeType = $fileMetadata['mime_type'] ?? 'application/octet-stream';
        $fileSize = Storage::disk('sigap')->size($filePath);
        
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];
        
        return Storage::disk('sigap')->response($filePath, null, $headers);
    }
    
    /**
     * Get file info
     */
    public function info(Request $request, $answerId, $fileIndex = null)
    {
        $answer = FormAnswer::findOrFail($answerId);
        
        // Check permission
        if (!$this->userCanAccessFile($answer)) {
            abort(403);
        }
        
        $fileMetadata = $this->getFileMetadata($answer, $fileIndex);
        
        return response()->json([
            'success' => true,
            'file' => $fileMetadata
        ]);
    }
    
    /**
     * Helper: Check if user can access file
     */
    private function userCanAccessFile(FormAnswer $answer): bool
    {
        $user = auth()->user();
        $submission = $answer->submission;
        
        // Check if user is submitter
        if ($submission->submitted_by == $user->id) {
            return true;
        }
        
        // Check if user is admin
        if ($user->hasAnyRole(['Super Admin', 'Owner'])) {
            return true;
        }
        
        // Check department access
        $submitterDeptIds = $submission->submitter->departments->pluck('id');
        $viewerDeptIds = $user->departments->pluck('id');
        
        return $submitterDeptIds->intersect($viewerDeptIds)->isNotEmpty();
    }
    
    /**
     * Helper: Get file path from answer
     */
    private function getFilePath(FormAnswer $answer, $fileIndex = null): ?string
    {
        $metadata = $answer->answer_metadata ?? [];
        
        // Handle live photos
        if (isset($metadata['live_photo']) && $metadata['live_photo']) {
            $photos = $metadata['photos'] ?? [];
            $index = $fileIndex ?? 0;
            
            return $photos[$index]['file_path'] ?? null;
        }
        
        if (isset($metadata['multiple']) && $metadata['multiple']) {
            // Multiple files
            $paths = json_decode($answer->answer_value, true);
            $index = $fileIndex ?? 0;
            
            return $paths[$index] ?? null;
        } else {
            // Single file
            return $answer->answer_value;
        }
    }
    
    /**
     * Helper: Get file metadata
     */
    private function getFileMetadata(FormAnswer $answer, $fileIndex = null): array
    {
        $metadata = $answer->answer_metadata ?? [];
        
        // Handle live photos
        if (isset($metadata['live_photo']) && $metadata['live_photo']) {
            $photos = $metadata['photos'] ?? [];
            $index = $fileIndex ?? 0;
            $photo = $photos[$index] ?? [];
            
            return [
                'original_name' => $photo['original_filename'] ?? 'live_photo.jpg',
                'mime_type' => 'image/jpeg',
                'size' => $photo['file_size'] ?? null,
                'captured_at' => $photo['captured_at'] ?? null,
                'user_name' => $photo['user_name'] ?? null,
                'camera_type' => $photo['camera_type'] ?? 'rear'
            ];
        }
        
        if (isset($metadata['multiple']) && $metadata['multiple']) {
            // Multiple files
            $files = $metadata['files'] ?? [];
            $index = $fileIndex ?? 0;
            
            return $files[$index] ?? [];
        } else {
            // Single file
            return $metadata;
        }
    }

    /**
     * Download watermarked image
     */
    private function downloadWatermarkedImage($filePath, $fileMetadata, $fileName)
    {
        try {            
            // Read and process image
            $imageContent = Storage::disk('sigap')->get($filePath);
            $image = $this->imageManager->read($imageContent);
            
            // Add download-specific watermark
            $this->addDownloadWatermark($image);
            
            // Encode with high quality
            $encoded = $image->toJpeg(95);
            
            // Return download response
            return response($encoded, 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Download watermark failed: ' . $e->getMessage());
            
            // Return original file if watermarking fails
            return Storage::disk('sigap')->download($filePath, $fileName, [
                'Content-Type' => $fileMetadata['mime_type'] ?? 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }
    }

    /**
     * Add comprehensive download watermark
     */
    private function addDownloadWatermark($image)
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        
        try {
            $fontPath = public_path('fonts/Montserrat-VariableFont_wght.ttf');
            
            // 1. Add center diagonal watermark
            $this->addCenterWatermark($image, $imageWidth, $imageHeight, $fontPath);
            
            // 2. Add corner identification
            $this->addCornerWatermark($image, $imageWidth, $imageHeight, $fontPath);
            
            // 3. Add download timestamp
            $this->addDownloadTimestamp($image, $imageWidth, $imageHeight, $fontPath);
            
            // 4. Add user identification
            $this->addUserWatermark($image, $imageWidth, $imageHeight, $fontPath);
            
        } catch (\Exception $e) {
            \Log::error('Comprehensive watermark failed: ' . $e->getMessage());
        }
    }

    /**
     * Add center diagonal watermark
     */
    private function addCenterWatermark($image, $imageWidth, $imageHeight, $fontPath)
    {
        $text = 'PT SIAP - DOWNLOADED';
        $fontSize = max(20, $imageWidth / 15);
        $x = $imageWidth / 2;
        $y = $imageHeight / 2;
        
        // Background text (shadow)
        $image->text($text, $x + 3, $y + 3, function (FontFactory $font) use ($fontSize, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color('rgba(0, 0, 0, 0.8)');
            $font->align('center');
            $font->valign('middle');
            $font->angle(45);
        });
        
        // Main text
        $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color('rgba(255, 255, 255, 0.9)');
            $font->align('center');
            $font->valign('middle');
            $font->angle(45);
        });
    }

    /**
     * Add corner watermark
     */
    private function addCornerWatermark($image, $imageWidth, $imageHeight, $fontPath)
    {
        $text = 'PT SIAP';
        $fontSize = max(12, $imageWidth / 40);
        $x = $imageWidth - 15;
        $y = $imageHeight - 15;
        
        $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color('rgba(0, 0, 0, 0.9)');
            $font->align('right');
            $font->valign('bottom');
        });
    }

    /**
     * Add download timestamp
     */
    private function addDownloadTimestamp($image, $imageWidth, $imageHeight, $fontPath)
    {
        $timestamp = 'Downloaded: ' . now()->format('d/m/Y H:i');
        $fontSize = max(10, $imageWidth / 60);
        
        $image->text($timestamp, 15, $fontSize + 15, function (FontFactory $font) use ($fontSize, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color('rgba(0, 0, 0, 0.8)');
            $font->align('left');
            $font->valign('top');
        });
    }

    /**
     * Add user identification watermark
     */
    private function addUserWatermark($image, $imageWidth, $imageHeight, $fontPath)
    {
        $userText = 'Downloaded by: ' . auth()->user()->name;
        $fontSize = max(10, $imageWidth / 50);
        $y = $imageHeight - 15;
        
        $image->text($userText, 15, $y, function (FontFactory $font) use ($fontSize, $fontPath) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color('rgba(0, 0, 0, 0.8)');
            $font->align('left');
            $font->valign('bottom');
        });
    }

    
}