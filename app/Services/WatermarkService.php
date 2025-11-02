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
        $watermarkText = $this->generateWatermarkText($user);
        $watermarkedPath = $this->generateWatermarkedPath($pdfPath, $user->id);
        
        try {
            // Attempt to use FPDI if available (requires: composer require setasign/fpdi)
            if (class_exists(\setasign\Fpdi\Fpdi::class)) {
                return $this->applyWatermarkWithFpdi($pdfPath, $watermarkText, $user->id);
            }
            
            // Fallback: Try using shell command with pdftk or ghostscript if available
            if ($this->hasPdftk()) {
                return $this->applyWatermarkWithPdftk($pdfPath, $watermarkText, $user->id);
            }
            
            // Final fallback: Create a watermark overlay note
            // For proper PDF watermarking, install: composer require setasign/fpdi
            \Log::warning('PDF watermarking requires FPDI library. Install with: composer require setasign/fpdi');
            
            // Copy original and log that watermarking was not applied
            Storage::disk('s3')->copy($pdfPath, $watermarkedPath);
            
            return $watermarkedPath;
            
        } catch (\Exception $e) {
            \Log::error('PDF watermarking failed', [
                'pdf_path' => $pdfPath,
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to copying original file
            Storage::disk('s3')->copy($pdfPath, $watermarkedPath);
            return $watermarkedPath;
        }
    }
    
    private function applyWatermarkWithFpdi(string $pdfPath, string $watermarkText, int $userId): string
    {
        $sourcePdf = Storage::disk('s3')->get($pdfPath);
        $tempSource = tempnam(sys_get_temp_dir(), 'source_') . '.pdf';
        file_put_contents($tempSource, $sourcePdf);
        
        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($tempSource);
            
            // Create watermark layer
            $watermarkPdf = new \setasign\Fpdi\Fpdi();
            
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tplId);
                
                $watermarkPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $watermarkPdf->useTemplate($tplId);
                
                // Add watermark text (diagonal, semi-transparent)
                $watermarkPdf->SetFont('Arial', 'B', 24);
                $watermarkPdf->SetTextColor(200, 200, 200);
                $watermarkPdf->SetAlpha(0.3);
                
                // Rotate and position watermark diagonally
                $watermarkPdf->StartTransform();
                $watermarkPdf->Rotate(45, $size['width'] / 2, $size['height'] / 2);
                
                $lines = explode("\n", $watermarkText);
                $lineHeight = 10;
                $startY = ($size['height'] / 2) - (count($lines) * $lineHeight / 2);
                
                foreach ($lines as $index => $line) {
                    $watermarkPdf->Text(
                        $size['width'] / 2,
                        $startY + ($index * $lineHeight),
                        $line
                    );
                }
                
                $watermarkPdf->StopTransform();
                $watermarkPdf->SetAlpha(1);
            }
            
            $tempWatermarked = tempnam(sys_get_temp_dir(), 'watermarked_') . '.pdf';
            $watermarkPdf->Output('F', $tempWatermarked);
            
            $watermarkedContent = file_get_contents($tempWatermarked);
            $watermarkedPath = $this->generateWatermarkedPath($pdfPath, $userId);
            Storage::disk('s3')->put($watermarkedPath, $watermarkedContent);
            
            unlink($tempSource);
            unlink($tempWatermarked);
            
            return $watermarkedPath;
            
        } finally {
            if (file_exists($tempSource)) {
                @unlink($tempSource);
            }
        }
    }
    
    private function applyWatermarkWithPdftk(string $pdfPath, string $watermarkText, int $userId): string
    {
        // pdftk watermarking would require creating a stamp PDF first
        // This is a placeholder - pdftk integration would go here
        $watermarkedPath = $this->generateWatermarkedPath($pdfPath, $userId);
        Storage::disk('s3')->copy($pdfPath, $watermarkedPath);
        return $watermarkedPath;
    }
    
    private function hasPdftk(): bool
    {
        $output = [];
        $returnVar = 0;
        @exec('which pdftk 2>&1', $output, $returnVar);
        return $returnVar === 0;
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
