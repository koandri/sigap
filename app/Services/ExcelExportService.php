<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\FormRequest;
use App\Models\PrintedForm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ExcelExportService implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly Collection $data,
        private readonly string $title,
        private readonly array $headings,
        private readonly array $mapping
    ) {}

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        $mapped = [];
        foreach ($this->mapping as $key => $value) {
            if (is_callable($value)) {
                $mapped[$key] = $value($row);
            } else {
                $mapped[$key] = $row->$value ?? '';
            }
        }
        return $mapped;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return $this->title;
    }

    public static function exportDocumentsMasterlist(Collection $documents): self
    {
        return new self(
            $documents,
            'Documents Masterlist',
            [
                'Document Number',
                'Title',
                'Type',
                'Department',
                'Current Version',
                'Status',
                'Created By',
                'Created At',
                'Physical Location',
            ],
            [
                'document_number',
                'title',
                'document_type',
                'department.name',
                'activeVersion.version_number',
                'activeVersion.status',
                'createdBy.name',
                'created_at',
                'physical_location',
            ]
        );
    }

    public static function exportFormRequests(Collection $formRequests): self
    {
        return new self(
            $formRequests,
            'Form Requests',
            [
                'Request Number',
                'Requested By',
                'Request Date',
                'Status',
                'Items Count',
                'Total Quantity',
                'Acknowledged At',
                'Ready At',
                'Collected At',
            ],
            [
                'id',
                'requestedBy.name',
                'request_date',
                'status',
                'items_count',
                'total_quantity',
                'acknowledged_at',
                'ready_at',
                'collected_at',
            ]
        );
    }

    public static function exportPrintedForms(Collection $printedForms): self
    {
        return new self(
            $printedForms,
            'Printed Forms',
            [
                'Form Number',
                'Document',
                'Issued To',
                'Issued At',
                'Status',
                'Returned At',
                'Received At',
                'Scanned At',
            ],
            [
                'form_number',
                'documentVersion.document.title',
                'issuedTo.name',
                'issued_at',
                'status',
                'returned_at',
                'received_at',
                'scanned_at',
            ]
        );
    }
}
