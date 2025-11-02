
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - {{ $submission->submission_code }}</title>
    <link rel="stylesheet" href="/assets/css/formsubmissions_print.css" />
    <style>
    .formatted-content {
        max-width: 100%;
        word-wrap: break-word;
    }

    .formatted-content p {
        margin-bottom: 1rem;
    }

    .formatted-content ul, .formatted-content ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .formatted-content h1, .formatted-content h2, .formatted-content h3, 
    .formatted-content h4, .formatted-content h5, .formatted-content h6 {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .formatted-content blockquote {
        margin: 1rem 0;
        padding: 0.5rem 1rem;
        border-left: 4px solid #dee2e6;
        background-color: #f8f9fa;
    }

    .formatted-content table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }

    .formatted-content table th,
    .formatted-content table td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
    }

    .formatted-content table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    </style>
</head>
<body>
    <!-- Print Button (hidden when printing) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            🖨️ Print Document
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            ✖️ Close
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>PT. SURYA INTI ANEKA PANGAN</h1>
        <p>FORM SUBMISSION RECORD</p>
        <p><strong>{{ $submission->formVersion->form->name }}</strong></p>
    </div>

    <!-- Submission Information -->
    <div class="info-section">
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="info-label">Submission Code:</span>
                    <span class="info-value">{{ $submission->submission_code }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Form Number:</span>
                    <span class="info-value">{{ $submission->formVersion->form->form_no }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Version:</span>
                    <span class="info-value">v{{ $submission->formVersion->version_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-{{ $submission->status }}">
                        {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                    </span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="info-label">Submitted By:</span>
                    <span class="info-value">{{ $submission->submitter->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Department:</span>
                    <span class="info-value">
                        {{ $submission->submitter->departments->pluck('name')->join(', ') }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Submitted At:</span>
                    <span class="info-value">
                        {{ formatDate($submission->submitted_at, 'd M Y H:i') . ' WIB' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Completed At:</span>
                    <span class="info-value">
                        {{ formatDate($submission->completed_at, 'd M Y H:i') . ' WIB' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Answers -->
    <div class="answers-section">
        <h2 style="color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">
            Form Responses
        </h2>
        
        @foreach($submission->formVersion->getFieldsInOrder() as $field)
            @php
                $answer = $submission->answers->where('form_field_id', $field->id)->first();
            @endphp
            <div class="answer-item">
                <div class="answer-label {{ $field->is_required ? 'required' : '' }}">
                    {{ $field->field_label }}
                </div>
                <div class="answer-value">
                    @if($answer)
                        @switch($field->field_type)
                            @case('file')
                                <em>File: {{ $answer->answer_metadata['original_name'] ?? 'Attached' }}</em>
                                @break
                            
                            @case('select_multiple')
                            @case('checkbox')
                                @php
                                    $values = json_decode($answer->answer_value, true);
                                @endphp
                                @if(is_array($values))
                                    @foreach($values as $value)
                                        @php
                                            $option = $field->options->where('option_value', $value)->first();
                                        @endphp
                                        • {{ $option ? $option->option_label : $value }}<br>
                                    @endforeach
                                @else
                                    {{ $answer->answer_value }}
                                @endif
                                @break
                            
                            @case('select_single')
                            @case('radio')
                                @php
                                    $option = $field->options->where('option_value', $answer->answer_value)->first();
                                @endphp
                                {{ $option ? $option->option_label : $answer->answer_value }}
                                @break
                            
                            @case('boolean')
                                {{ $answer->answer_value == '1' ? 'Yes' : 'No' }}
                                @break
                            
                            @case('text_long')
                                <div class="formatted-content">{!! $answer->answer_value !!}</div>
                                @break

                            @case('calculated')
                                @php
                                    $calculationRules = $field->validation_rules ?? [];
                                    $format = $calculationRules['format'] ?? 'number';
                                    $rawValue = $answer ? (float)$answer->answer_value : 0;
                                    $calculationService = app(\App\Services\CalculationService::class);
                                    $formattedValue = $calculationService->formatValue($rawValue, $format);
                                @endphp
                                    {{ $formattedValue }}
                                    <span style="font-size: 10px; color: #666; margin-left: 10px;">
                                        (Calculated: {{ $field->calculation_formula }})
                                    </span>
                                @break

                            @case('signature')
                                <div style="margin-left: 20px; page-break-inside: avoid;">
                                    @if($answer && $answer->answer_value)
                                        <div style="text-align: center; margin: 20px 0;">
                                            <img src="{{ Storage::disk('sigap')->url($answer->answer_value) }}" 
                                                alt="Digital Signature" 
                                                style="max-width: 300px; max-height: 150px; border: 1px solid #000;">
                                            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                                                <strong>Digital Signature</strong><br>
                                                Signed by: {{ $answer->answer_metadata['signed_by'] ?? 'Unknown' }}<br>
                                                Date: {{ isset($answer->answer_metadata['signed_at']) ? formatDate(\Carbon\Carbon::parse($answer->answer_metadata['signed_at']), 'd M Y H:i') : 'Unknown' }}
                                            </div>
                                        </div>
                                    @else
                                        <div style="border: 1px dashed #ccc; height: 100px; display: flex; align-items: center; justify-content: center; color: #999; font-style: italic;">
                                            Signature not provided
                                        </div>
                                    @endif
                                </div>
                                @break

                            @case('live_photo')
                                <div style="margin-left: 20px; page-break-inside: avoid;">
                                    @if($answer && $answer->answer_value)
                                        @php
                                            $metadata = $answer->answer_metadata ?? [];
                                            $photos = $metadata['photos'] ?? [];
                                            $totalPhotos = $metadata['total_photos'] ?? 0;
                                        @endphp
                                        
                                        @if(!empty($photos))
                                            <div style="text-align: center; margin: 20px 0;">
                                                @if($totalPhotos > 1)
                                                    <div style="font-size: 14px; color: #666; margin-bottom: 15px;">
                                                        <strong>{{ $totalPhotos }} Live Photos Captured</strong>
                                                    </div>
                                                    @foreach($photos as $index => $photo)
                                                        <div style="margin-bottom: 20px; page-break-inside: avoid;">
                                                            <img src="{{ Storage::disk('sigap')->url($photo['file_path']) }}" 
                                                                alt="Live Photo {{ $index + 1 }}" 
                                                                style="max-width: 300px; max-height: 200px; border: 1px solid #000;">
                                                            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                                                                <strong>Live Photo {{ $index + 1 }}</strong><br>
                                                                Captured by: {{ $photo['user_name'] ?? 'Unknown User' }}<br>
                                                                Date: {{ isset($photo['captured_at']) ? formatDate(\Carbon\Carbon::parse($photo['captured_at']), 'd M Y H:i') : 'Unknown' }}<br>
                                                                Camera: {{ $photo['camera_type'] ?? 'Rear' }} Camera
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    @php $photo = $photos[0]; @endphp
                                                    <img src="{{ Storage::disk('sigap')->url($photo['file_path']) }}" 
                                                        alt="Live Photo" 
                                                        style="max-width: 300px; max-height: 200px; border: 1px solid #000;">
                                                    <div style="margin-top: 10px; font-size: 12px; color: #666;">
                                                        <strong>Live Photo</strong><br>
                                                        Captured by: {{ $photo['user_name'] ?? 'Unknown User' }}<br>
                                                        Date: {{ isset($photo['captured_at']) ? \Carbon\Carbon::parse($photo['captured_at'])->format('d M Y H:i') : 'Unknown' }}<br>
                                                        Camera: {{ $photo['camera_type'] ?? 'Rear' }} Camera
                                                        @if(isset($metadata['watermarked']) && $metadata['watermarked'])
                                                            <br>Watermarked with user info and EXIF data
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div style="border: 1px dashed #ccc; height: 100px; display: flex; align-items: center; justify-content: center; color: #999; font-style: italic;">
                                                No live photos captured
                                            </div>
                                        @endif
                                    @else
                                        <div style="border: 1px dashed #ccc; height: 100px; display: flex; align-items: center; justify-content: center; color: #999; font-style: italic;">
                                            No live photos captured
                                        </div>
                                    @endif
                                </div>
                                @break
                            
                            @default
                                {{ $answer->answer_value }}
                        @endswitch
                    @else
                        <span style="color: #999;">Not answered</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Signature Section -->
    <div class="authorisation-section">
        <h2 style="color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">
            Authorisation Signatures
        </h2>
    </div>
    @php
        $approvalHistory = $submission->approvalHistory()->with(['step', 'approver', 'assignedUser'])->get();
        $workflow = $submission->formVersion->form->activeApprovalWorkflow;
    @endphp
    @if($submission->formVersion->form->requires_approval && $submission->status === 'approved')
     
    <div class="signature-section" style="margin-top: 10px; page-break-inside: avoid;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
            <!-- Submitter Signature -->
            <div class="signature-box" style="text-align: center;">
                <p style="margin-bottom: 5px; font-weight: bold;">Submitted By:</p>
                <div style="border-bottom: 1px solid #333; margin-bottom: 5px; height: 60px; display: flex; align-items: end; justify-content: center;">
                    <span style="font-size: 12px; color: #666; margin-bottom: 5px;"></span>
                </div>
                <p style="margin: 0; font-weight: bold;">{{ $submission->submitter->name }}</p>
                <p style="margin: 0; font-size: 12px; color: #666;">
                    {{ formatDate($submission->submitted_at, 'd M Y H:i') }}
                </p>
            </div>
            
            <!-- Final Approver Signature -->
            @php
                $finalApproval = $approvalHistory->where('status', 'approved')->sortByDesc('action_at')->first();
            @endphp
            <div class="signature-box" style="text-align: center;">
                <p style="margin-bottom: 5px; font-weight: bold;">Final Approval:</p>
                <div style="border-bottom: 1px solid #333; margin-bottom: 5px; height: 60px; display: flex; align-items: end; justify-content: center;">
                    @if($finalApproval)
                        <span style="font-size: 12px; color: #666; margin-bottom: 5px;"></span>
                    @else
                        <span style="font-size: 12px; color: #666; margin-bottom: 5px;">Pending Approval</span>
                    @endif
                </div>
                <p style="margin: 0; font-weight: bold;">
                    {{ $finalApproval?->approver->name ?? '_____________________' }}
                </p>
                <p style="margin: 0; font-size: 12px; color: #666;">
                    {{ $finalApproval?->action_at ? formatDate($finalApproval->action_at, 'd M Y H:i') : 'Date: _______________' }}
                </p>
            </div>
        </div>
        
        <!-- Additional Approvers (if multiple) -->
        @php
            $allApprovers = $approvalHistory->where('status', 'approved')->sortBy('action_at');
        @endphp
        
        @if($allApprovers->count() > 1)
        <div style="margin-top: 30px;">
            <h3 style="font-size: 14px; color: #495057; margin-bottom: 10px;">Additional Approvers:</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                @foreach($allApprovers->take(4) as $approval)
                    <div class="mini-signature-box" style="text-align: center; border: 1px solid #dee2e6; padding: 10px; border-radius: 3px;">
                        <p style="margin: 0; font-size: 11px; font-weight: bold;">{{ $approval->step->step_name }}</p>
                        <div style="border-bottom: 1px solid #666; margin: 8px 0; height: 30px;"></div>
                        <p style="margin: 0; font-size: 10px; font-weight: bold;">{{ $approval->approver->name }}</p>
                        <p style="margin: 0; font-size: 9px; color: #666;">{{ formatDate($approval->action_at, 'd M Y') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Approval History Table (Detailed) -->
    @if($submission->needsApproval() && $approvalHistory->count() > 0)
    <div class="approval-history-section" style="margin-top: 30px; page-break-before: auto;">
        <h2 style="color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px;">
            Detailed Approval History
        </h2>
        
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Step</th>
                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Approver</th>
                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Status</th>
                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Assigned</th>
                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Processed</th>
                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Comments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvalHistory->sortBy('created_at') as $log)
                <tr>
                    <td style="border: 1px solid #dee2e6; padding: 6px;">
                        <strong>{{ $log->step->step_name }}</strong>
                        <br>
                        <small style="color: #666;">Step {{ $log->step->getStepPosition() }}</small>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 6px;">
                        <strong>{{ $log->assignedUser->name }}</strong>
                        @if($log->approver && $log->approved_by != $log->assigned_to)
                            <br><small style="color: #666;">Processed by: {{ $log->approver->name }}</small>
                        @endif
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: center;">
                        <span style="font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 3px; text-transform: uppercase;
                                    {{ $log->status === 'approved' ? 'background: #d4edda; color: #155724;' : 
                                        ($log->status === 'rejected' ? 'background: #f8d7da; color: #721c24;' : 
                                        ($log->status === 'pending' ? 'background: #fff3cd; color: #856404;' : 'background: #e2e3e5; color: #383d41;')) }}">
                            {{ $log->status }}
                        </span>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: center;">
                        {{ formatDate($log->assigned_at, 'd/m/Y H:i') }}
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: center;">
                        {{ formatDate($log->action_at, 'd/m/Y H:i') }}
                        @if($log->due_at && $log->status === 'pending')
                            <br><small style="color: {{ $log->due_at < now() ? '#dc3545' : '#666' }};">
                                Due: {{ formatDate($log->due_at, 'd/m H:i') }}
                            </small>
                        @endif
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 6px;">
                        {{ $log->comments ?: '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Approval Summary -->
        <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 3px;">
            <div style="font-size: 10px; color: #666; text-align: center;">
                <strong>Approval Summary:</strong>
                Approved: {{ $approvalHistory->where('status', 'approved')->count() }} | 
                Rejected: {{ $approvalHistory->where('status', 'rejected')->count() }} | 
                Pending: {{ $approvalHistory->where('status', 'pending')->count() }}
                @if($submission->completed_at)
                    | <strong>Workflow Duration:</strong> {{ $submission->submitted_at->diffInHours($submission->completed_at) }} hours
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This document was generated for {{ auth()->user()->name }} on {{ formatDate(now(), 'd M Y H:i:s') }} WIB</p>
        <p>PT. Surya Inti Aneka Pangan - SIGaP</p>
    </div>
</body>
</html>