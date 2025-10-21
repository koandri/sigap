<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

final class WatermarkService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function applyWatermark(string $filePath, User $user): string
    {
        // Load the PDF or image file
        $image = $this->imageManager->read(Storage::disk('s3')->get($filePath));
        
        // Create watermark text
        $watermarkText = $this->generateWatermarkText($user);
        
        // Apply watermark
        $watermarkedImage = $this->addWatermarkToImage($image, $watermarkText);
        
        // Save watermarked file
        $watermarkedPath = $this->generateWatermarkedPath($filePath, $user->id);
        $watermarkedImage->save(Storage::disk('s3')->path($watermarkedPath));
        
        return $watermarkedPath;
    }

    public function applyWatermarkToPdf(string $pdfPath, User $user): string
    {
        // For PDF files, we need to use a different approach
        // This is a simplified version - in production, you might want to use
        // a library like TCPDF or FPDF for better PDF watermarking
        
        $watermarkText = $this->generateWatermarkText($user);
        $watermarkedPath = $this->generateWatermarkedPath($pdfPath, $user->id);
        
        // For now, we'll just copy the file and add a text overlay
        // In a real implementation, you'd use a PDF library to add watermarks
        Storage::disk('s3')->copy($pdfPath, $watermarkedPath);
        
        return $watermarkedPath;
    }

    public function generateWatermarkText(User $user): string
    {
        $currentDateTime = now()->format('Y-m-d H:i:s');
        
        return sprintf(
            "CONFIDENTIAL\n%s\n%s\nPT. Surya Inti Aneka Pangan",
            $user->name,
            $currentDateTime
        );
    }

    public function createWatermarkedPdf(string $originalPdfPath, User $user): string
    {
        // This is a placeholder implementation
        // In production, you would use a proper PDF library like TCPDF or FPDF
        // to create a new PDF with watermarks
        
        $watermarkText = $this->generateWatermarkText($user);
        $watermarkedPath = $this->generateWatermarkedPath($originalPdfPath, $user->id);
        
        // For now, we'll just copy the original file
        // TODO: Implement proper PDF watermarking
        Storage::disk('s3')->copy($originalPdfPath, $watermarkedPath);
        
        return $watermarkedPath;
    }

    private function addWatermarkToImage($image, string $watermarkText)
    {
        // Get image dimensions
        $width = $image->width();
        $height = $image->height();
        
        // Create watermark overlay
        $watermark = $this->imageManager->create($width, $height);
        $watermark->fill('rgba(0, 0, 0, 0.1)');
        
        // Add text to watermark
        $watermark->text($watermarkText, $width / 2, $height / 2, function ($font) {
            $font->filename(public_path('fonts/arial.ttf')); // You'll need to provide a font file
            $font->size(24);
            $font->color('rgba(0, 0, 0, 0.3)');
            $font->align('center');
            $font->valign('middle');
            $font->angle(45); // Diagonal watermark
        });
        
        // Composite watermark onto original image
        return $image->place($watermark, 'center', 50, 50);
    }

    private function generateWatermarkedPath(string $originalPath, int $userId): string
    {
        $pathInfo = pathinfo($originalPath);
        $extension = $pathInfo['extension'];
        $filename = $pathInfo['filename'];
        
        return sprintf(
            'documents/watermarked/%s_%d_%s.%s',
            $filename,
            $userId,
            time(),
            $extension
        );
    }

    public function cleanupExpiredWatermarks(): int
    {
        // Clean up watermarked files older than 24 hours
        $expiredFiles = Storage::disk('s3')
            ->files('documents/watermarked/')
            ->filter(function ($file) {
                $lastModified = Storage::disk('s3')->lastModified($file);
                return $lastModified < now()->subHours(24)->timestamp;
            });

        $count = $expiredFiles->count();
        
        foreach ($expiredFiles as $file) {
            Storage::disk('s3')->delete($file);
        }
        
        return $count;
    }
}
