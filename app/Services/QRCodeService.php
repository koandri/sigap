<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FormRequest;
use App\Models\PrintedForm;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

final class QRCodeService
{
    public function generateFormLabel(FormRequest $request): string
    {
        $printedForms = $request->items->flatMap(function ($item) {
            return $item->printedForms;
        });

        $labels = $printedForms->flatMap(function ($printedForm) {
            $labelData = $this->generateSingleLabel($printedForm);
            
            // If NCR paper, generate 3 copies of the label
            if ($printedForm->documentVersion->is_ncr_paper) {
                return collect()->times(3, fn() => $labelData);
            }
            
            return [$labelData];
        });

        // Generate PDF with all labels
        $pdf = Pdf::loadView('form-requests.labels', [
            'labels' => $labels,
            'request' => $request,
        ]);

        $filename = 'form-labels-' . $request->id . '-' . time() . '.pdf';
        $filePath = 'documents/labels/' . $filename;
        
        Storage::disk('s3')->put($filePath, $pdf->output(), 'public');
        
        return $filePath;
    }

    public function generateSingleLabel(PrintedForm $printedForm): array
    {
        $qrCode = $this->generateQRCode($printedForm);
        
        return [
            'form_number' => $printedForm->form_number,
            'form_name' => $printedForm->form_name,
            'issue_date' => $printedForm->issue_date,
            'qr_code' => $qrCode,
            'url' => route('printed-forms.show', $printedForm),
        ];
    }

    public function generateQRCode(PrintedForm $printedForm): string
    {
        $url = route('printed-forms.show', $printedForm);
        
        $qrCode = QrCode::create($url)
            ->setSize(200)
            ->setMargin(10);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    public function generateQRCodeForUrl(string $url): string
    {
        $qrCode = QrCode::create($url)
            ->setSize(200)
            ->setMargin(10);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    public function generatePrintableLabels(FormRequest $request): string
    {
        $printedForms = $request->items->flatMap(function ($item) {
            return $item->printedForms;
        });

        $labels = $printedForms->flatMap(function ($printedForm) {
            $labelData = [
                'form_number' => $printedForm->form_number,
                'form_name' => $printedForm->form_name,
                'issue_date' => $printedForm->issue_date,
                'qr_code' => $this->generateQRCode($printedForm),
            ];
            
            // If NCR paper, generate 3 copies of the label
            if ($printedForm->documentVersion->is_ncr_paper) {
                return collect()->times(3, fn() => $labelData);
            }
            
            return [$labelData];
        });

        // Generate PDF with labels in a grid format
        $pdf = Pdf::loadView('form-requests.printable-labels', [
            'labels' => $labels,
            'request' => $request,
        ])->setPaper('A4', 'portrait');

        $filename = 'printable-labels-' . $request->id . '-' . time() . '.pdf';
        $filePath = 'documents/labels/' . $filename;
        
        Storage::disk('s3')->put($filePath, $pdf->output(), 'public');
        
        return $filePath;
    }

    public function generateBulkLabels(array $printedFormIds): string
    {
        $printedForms = PrintedForm::whereIn('id', $printedFormIds)
            ->with('documentVersion')
            ->get();
        
        $labels = $printedForms->flatMap(function ($printedForm) {
            $labelData = [
                'form_number' => $printedForm->form_number,
                'form_name' => $printedForm->form_name,
                'issue_date' => $printedForm->issue_date,
                'qr_code' => $this->generateQRCode($printedForm),
            ];
            
            // If NCR paper, generate 3 copies of the label
            if ($printedForm->documentVersion->is_ncr_paper) {
                return collect()->times(3, fn() => $labelData);
            }
            
            return [$labelData];
        });

        $pdf = Pdf::loadView('form-requests.bulk-labels', [
            'labels' => $labels,
        ])->setPaper('A4', 'portrait');

        $filename = 'bulk-labels-' . time() . '.pdf';
        $filePath = 'documents/labels/' . $filename;
        
        Storage::disk('s3')->put($filePath, $pdf->output(), 'public');
        
        return $filePath;
    }

    public function cleanupOldLabels(): int
    {
        // Clean up label files older than 7 days
        $expiredFiles = Storage::disk('s3')
            ->files('documents/labels/')
            ->filter(function ($file) {
                $lastModified = Storage::disk('s3')->lastModified($file);
                return $lastModified < now()->subDays(7)->timestamp;
            });

        $count = $expiredFiles->count();
        
        foreach ($expiredFiles as $file) {
            Storage::disk('s3')->delete($file);
        }
        
        return $count;
    }
}
