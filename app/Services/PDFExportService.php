<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FormRequest;
use App\Models\PrintedForm;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

final class PDFExportService
{
    public function exportFormRequestLabels(FormRequest $formRequest): string
    {
        $labels = $formRequest->printedForms()
            ->with(['documentVersion.document', 'issuedTo'])
            ->get();

        $pdf = Pdf::loadView('exports.form-request-labels', [
            'formRequest' => $formRequest,
            'labels' => $labels,
        ]);

        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->output();
    }

    public function exportPrintedFormLabels(Collection $printedForms): string
    {
        $pdf = Pdf::loadView('exports.printed-form-labels', [
            'printedForms' => $printedForms,
        ]);

        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->output();
    }

    public function exportDocumentMasterlist(Collection $documents): string
    {
        $pdf = Pdf::loadView('exports.documents-masterlist', [
            'documents' => $documents,
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->output();
    }

    public function exportFormRequestReport(Collection $formRequests): string
    {
        $pdf = Pdf::loadView('exports.form-requests-report', [
            'formRequests' => $formRequests,
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->output();
    }

    public function exportPrintedFormReport(Collection $printedForms): string
    {
        $pdf = Pdf::loadView('exports.printed-forms-report', [
            'printedForms' => $printedForms,
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->output();
    }
}
