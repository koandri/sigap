<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FormRequestStatus;
use App\Models\FormRequest;
use App\Models\FormRequestItem;
use App\Models\PrintedForm;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

final class FormRequestService
{
    public function createFormRequest(User $user, array $formData): FormRequest
    {
        return DB::transaction(function () use ($user, $formData) {
            $request = FormRequest::create([
                'requested_by' => $user->id,
                'request_date' => now(),
                'status' => FormRequestStatus::Requested,
            ]);

            // Create form request items
            foreach ($formData['forms'] as $formData) {
                FormRequestItem::create([
                    'form_request_id' => $request->id,
                    'document_version_id' => $formData['document_version_id'],
                    'quantity' => $formData['quantity'],
                ]);
            }

            return $request;
        });
    }

    public function updateFormRequest(FormRequest $request, array $formData): FormRequest
    {
        return DB::transaction(function () use ($request, $formData) {
            // Delete existing items
            $request->items()->delete();

            // Create new form request items
            foreach ($formData['forms'] as $formItem) {
                FormRequestItem::create([
                    'form_request_id' => $request->id,
                    'document_version_id' => $formItem['document_version_id'],
                    'quantity' => $formItem['quantity'],
                ]);
            }

            return $request->fresh(['items.documentVersion.document']);
        });
    }

    public function acknowledgeRequest(FormRequest $request, User $acknowledger): void
    {
        DB::transaction(function () use ($request, $acknowledger) {
            $request->update([
                'acknowledged_at' => now(),
                'acknowledged_by' => $acknowledger->id,
                'status' => FormRequestStatus::Acknowledged,
            ]);
        });
    }

    public function processRequest(FormRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $request->update([
                'status' => FormRequestStatus::Processing,
            ]);
        });
    }

    public function markReady(FormRequest $request): void
    {
        DB::transaction(function () use ($request) {
            // Generate printed form records
            $this->generatePrintedForms($request);
            
            $request->update([
                'ready_at' => now(),
                'status' => FormRequestStatus::Ready,
            ]);
        });
    }

    public function markCollected(FormRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $request->update([
                'collected_at' => now(),
                'status' => FormRequestStatus::Collected,
            ]);
        });
    }

    public function generateFormNumbers(FormRequest $request): Collection
    {
        $printedForms = collect();
        
        foreach ($request->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $formNumber = $this->generateFormNumber();
                
                $printedForm = PrintedForm::create([
                    'form_request_item_id' => $item->id,
                    'form_number' => $formNumber,
                    'document_version_id' => $item->document_version_id,
                    'issued_to' => $request->requested_by,
                    'issued_at' => now(),
                    'status' => 'issued',
                ]);
                
                $printedForms->push($printedForm);
            }
        }
        
        return $printedForms;
    }

    public function markReturned(PrintedForm $printedForm, string $status, string $notes = null): void
    {
        DB::transaction(function () use ($printedForm, $status, $notes) {
            $printedForm->update([
                'status' => $status,
                'returned_at' => now(),
            ]);
        });
    }

    public function markReceived(PrintedForm $printedForm): void
    {
        DB::transaction(function () use ($printedForm) {
            $printedForm->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });
    }

    public function uploadScannedForm(PrintedForm $printedForm, string $filePath): void
    {
        DB::transaction(function () use ($printedForm, $filePath) {
            $printedForm->update([
                'status' => 'scanned',
                'scanned_file_path' => $filePath,
                'scanned_at' => now(),
            ]);
        });
    }

    public function calculateSLA(FormRequest $request): array
    {
        $metrics = [
            'request_to_acknowledgment' => null,
            'acknowledgment_to_ready' => null,
            'ready_to_collected' => null,
            'total_processing_time' => null,
        ];

        if ($request->acknowledged_at) {
            $metrics['request_to_acknowledgment'] = $request->acknowledged_at->diffInHours($request->request_date);
        }

        if ($request->ready_at && $request->acknowledged_at) {
            $metrics['acknowledgment_to_ready'] = $request->ready_at->diffInHours($request->acknowledged_at);
        }

        if ($request->collected_at && $request->ready_at) {
            $metrics['ready_to_collected'] = $request->collected_at->diffInHours($request->ready_at);
        }

        if ($request->collected_at) {
            $metrics['total_processing_time'] = $request->collected_at->diffInHours($request->request_date);
        }

        return $metrics;
    }

    public function getFormRequestsByUser(User $user): Collection
    {
        return FormRequest::with(['items.documentVersion.document', 'requester'])
            ->where('requested_by', $user->id)
            ->orderBy('request_date', 'desc')
            ->get();
    }

    public function getAllFormRequests(): Collection
    {
        return FormRequest::with(['items.documentVersion.document', 'requester'])
            ->orderBy('request_date', 'desc')
            ->get();
    }

    public function getPendingRequests(): Collection
    {
        return FormRequest::with(['items.documentVersion.document', 'requester'])
            ->where('status', FormRequestStatus::Requested)
            ->orderBy('request_date')
            ->get();
    }

    public function getReadyRequests(): Collection
    {
        return FormRequest::with(['items.documentVersion.document', 'requester'])
            ->where('status', FormRequestStatus::Ready)
            ->orderBy('ready_at')
            ->get();
    }

    public function getCirculatingForms(): Collection
    {
        return PrintedForm::with(['formRequestItem.formRequest.requester', 'documentVersion.document'])
            ->whereIn('status', ['issued', 'circulating'])
            ->orderBy('issued_at')
            ->get();
    }

    public function getOverdueRequests(): Collection
    {
        $cutoffTime = now()->subHours(2); // 2 hours SLA for acknowledgment
        
        return FormRequest::with(['items.documentVersion.document', 'requester'])
            ->where('status', FormRequestStatus::Requested)
            ->where('request_date', '<', $cutoffTime)
            ->orderBy('request_date')
            ->get();
    }

    public function getFilteredFormRequests(array $filters): Collection
    {
        $query = FormRequest::with(['items.documentVersion.document', 'requester']);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('request_date', 'desc')->get();
    }

    public function getFilteredFormRequestsByUser(User $user, array $filters): Collection
    {
        $query = FormRequest::with(['items.documentVersion.document', 'requester'])
            ->where('requested_by', $user->id);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('request_date', 'desc')->get();
    }

    public function getAllRequesters(): SupportCollection
    {
        return User::whereHas('formRequests')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function applyFilters($query, array $filters): void
    {
        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by requester (for admins)
        if (!empty($filters['requester'])) {
            $query->where('requested_by', $filters['requester']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('request_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('request_date', '<=', $filters['date_to']);
        }

        // Search by request ID
        if (!empty($filters['search'])) {
            $query->where('id', 'like', '%' . $filters['search'] . '%');
        }
    }

    private function generateFormNumber(): string
    {
        $date = now()->format('ymd');
        $prefix = "PF-{$date}-";
        
        // Get the last form number for today
        $lastForm = PrintedForm::where('form_number', 'like', $prefix . '%')
            ->orderBy('form_number', 'desc')
            ->first();
        
        if ($lastForm) {
            $lastNumber = (int) substr($lastForm->form_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function generatePrintedForms(FormRequest $request): void
    {
        foreach ($request->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                PrintedForm::create([
                    'form_request_item_id' => $item->id,
                    'form_number' => $this->generateFormNumber(),
                    'document_version_id' => $item->document_version_id,
                    'issued_to' => $request->requested_by,
                    'issued_at' => now(),
                    'status' => 'issued',
                ]);
            }
        }
    }
}
