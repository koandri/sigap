<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Services\HiddenFieldService;
use App\Services\ApprovalService;

use App\Models\User;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\FormSubmission;
use App\Models\FormAnswer;
use App\Models\FormField;
use App\Models\ApprovalLog;

class FormSubmissionController extends Controller
{
    protected HiddenFieldService $hiddenFieldService;
    protected ApprovalService $approvalService;

    public function __construct(HiddenFieldService $hiddenFieldService, ApprovalService $approvalService)
    {
        $this->hiddenFieldService = $hiddenFieldService;
        $this->approvalService = $approvalService;
    }

    /**
     * Display available forms for submission
     */
    public function index()
    {
        $user = auth()->user();
    
        // Check if user has Super Admin or Owner role
        $hasFullAccess = $user->hasAnyRole(['Super Admin', 'Owner']);
        
        if ($hasFullAccess) {
            // Show all active forms for Super Admin/Owner
            $forms = Form::where('is_active', true)
                ->whereHas('activeVersion', function($query) {
                    $query->where('is_active', true)
                        ->has('fields');
                })
                ->with(['activeVersion', 'departments'])
                ->get();
        } else {
            // Show only forms assigned to user's departments
            $userDepartmentIds = $user->departments->pluck('id');
            
            $forms = Form::where('is_active', true)
                ->whereHas('departments', function($query) use ($userDepartmentIds) {
                    $query->whereIn('departments.id', $userDepartmentIds);
                })
                ->whereHas('activeVersion', function($query) {
                    $query->where('is_active', true)
                        ->has('fields');
                })
                ->with(['activeVersion', 'departments'])
                ->get();
        }

        // Get user's recent submissions
        $recentSubmissions = FormSubmission::where('submitted_by', auth()->id())
            ->with(['formVersion.form'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('formsubmissions.index', compact('forms', 'recentSubmissions', 'hasFullAccess'));
    }

    /**
     * Show form to fill
     */
    public function create(Form $form, Request $request)
    {
        // Check if form is active
        if (!$form->is_active) {
            return redirect()->route('formsubmissions.index')
                ->with('error', 'This form is not active.');
        }

        // Get active version
        $version = $form->activeVersion;
        if (!$version) {
            return redirect()->route('formsubmissions.index')
                ->with('error', 'This form has no active version.');
        }

        // Check if version has fields
        if ($version->fields->count() == 0) {
            return redirect()->route('formsubmissions.index')
                ->with('error', 'This form has no fields defined.');
        }

        $user = auth()->user();
        
        // Check access - Super Admin/Owner can access all forms
        if (!$user->hasAnyRole(['Super Admin', 'Owner'])) {
            // Regular users need department access
            $userDepartmentIds = $user->departments->pluck('id');
            $formDepartmentIds = $form->departments->pluck('id');
            
            if ($userDepartmentIds->intersect($formDepartmentIds)->isEmpty()) {
                return redirect()->route('formsubmissions.index')
                    ->with('error', 'You do not have access to this form.');
            }
        }

        // Get fields with options
        $fields = $version->fields()
            ->with('options')
            ->ordered()
            ->get();

        // Process URL parameters for prefill
        $prefillData = $this->processPrefillParameters($request, $fields);

        return view('formsubmissions.create', compact('form', 'version', 'fields', 'prefillData'));
    }

    /**
     * Store form submission
     */
    public function store(Request $request, Form $form)
    {
        
        // Get active version
        $version = $form->activeVersion;
        if (!$version) {
            return redirect()->route('formsubmissions.index')
                ->with('error', 'This form has no active version.');
        }

        // Build validation rules based on fields
        $rules = [];
        $messages = [];
        $fields = $version->fields;
        $regularFields = $fields->where('field_type', '!=', 'calculated');

        // Determine if this is a draft save or submission
        $action = $request->input('action');
        $isDraft = $action === 'save_draft';
        

        foreach ($regularFields as $field) {
            $fieldRules = [];
            
            // For draft, make all fields optional
            // For submission, respect original required rules
            if (!$isDraft && $field->is_required) {
                $fieldRules[] = 'required';
                $messages["fields.{$field->field_code}.required"] = "{$field->field_label} is required.";
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-specific validation
            switch ($field->field_type) {
                case 'number':
                    $fieldRules[] = 'integer';
                    
                    // Apply custom validation rules for number fields
                    $validationRules = $field->validation_rules ?? [];
                    if (isset($validationRules['min'])) {
                        $fieldRules[] = 'min:' . $validationRules['min'];
                    }
                    if (isset($validationRules['max'])) {
                        $fieldRules[] = 'max:' . $validationRules['max'];
                    }
                    break;
                case 'decimal':
                    $fieldRules[] = 'numeric';
                    
                    // Apply custom validation rules for decimal fields
                    $validationRules = $field->validation_rules ?? [];
                    if (isset($validationRules['min'])) {
                        $fieldRules[] = 'min:' . $validationRules['min'];
                    }
                    if (isset($validationRules['max'])) {
                        $fieldRules[] = 'max:' . $validationRules['max'];
                    }
                    if (isset($validationRules['decimal_places'])) {
                        $decimalPlaces = $validationRules['decimal_places'];
                        $fieldRules[] = "regex:/^\d+(\.\d{1," . $decimalPlaces . "})?$/";
                    }
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    
                    // Apply custom date validation rules
                    $dateRules = $field->validation_rules ?? [];
                    if (isset($dateRules['date_min'])) {
                        $minRule = $dateRules['date_min'];
                        $minDate = $this->calculateDateFromRule($minRule);
                        if ($minDate) {
                            $fieldRules[] = 'after_or_equal:' . $minDate;
                        }
                    }
                    if (isset($dateRules['date_max'])) {
                        $maxRule = $dateRules['date_max'];
                        $maxDate = $this->calculateDateFromRule($maxRule);
                        if ($maxDate) {
                            $fieldRules[] = 'before_or_equal:' . $maxDate;
                        }
                    }
                    break;
                case 'datetime':
                    $fieldRules[] = 'date';
                    
                    // Apply custom datetime validation rules
                    $dateRules = $field->validation_rules ?? [];
                    if (isset($dateRules['date_min'])) {
                        $minRule = $dateRules['date_min'];
                        $minDate = $this->calculateDateFromRule($minRule);
                        if ($minDate) {
                            $fieldRules[] = 'after_or_equal:' . $minDate;
                        }
                    }
                    if (isset($dateRules['date_max'])) {
                        $maxRule = $dateRules['date_max'];
                        $maxDate = $this->calculateDateFromRule($maxRule);
                        if ($maxDate) {
                            $fieldRules[] = 'before_or_equal:' . $maxDate;
                        }
                    }
                    break;
                case 'file':
                    $fileRules = $field->validation_rules ?? [];
                    $allowMultiple = $fileRules['allow_multiple'] ?? false;
                    $maxFiles = $fileRules['max_files'] ?? 1;
                    $maxFileSize = $fileRules['max_file_size'] ?? 10240; // KB
                    $allowedExtensions = $fileRules['allowed_extensions'] ?? [];
                    
                    if ($allowMultiple) {
                        $fieldRules[] = 'array';
                        $fieldRules[] = 'max:' . $maxFiles;
                        $rules["fields.{$field->field_code}.*"] = [
                            'file',
                            'max:' . $maxFileSize
                        ];
                        
                        if (!empty($allowedExtensions)) {
                            $rules["fields.{$field->field_code}.*"][] = 'mimes:' . implode(',', $allowedExtensions);
                        }
                    } else {
                        $fieldRules[] = 'file';
                        $fieldRules[] = 'max:' . $maxFileSize;
                        
                        if (!empty($allowedExtensions)) {
                            $fieldRules[] = 'mimes:' . implode(',', $allowedExtensions);
                        }
                    }
                    break;
                case 'select_single':
                case 'radio':
                    if ($field->options->count() > 0) {
                        $fieldRules[] = 'in:' . $field->options->pluck('option_value')->implode(',');
                    }
                    break;
                case 'select_multiple':
                case 'checkbox':
                    $fieldRules[] = 'array';
                    if ($field->options->count() > 0) {
                        $fieldRules[] = 'in:' . $field->options->pluck('option_value')->implode(',');
                    }
                    break;
                case 'boolean':
                    $fieldRules[] = 'boolean';
                    break;
                case 'live_photo':
                    $photoRules = $field->validation_rules ?? [];
                    $maxPhotos = $photoRules['max_photos'] ?? 1;
                    
                    if ($maxPhotos > 1) {
                        $fieldRules[] = 'array';
                        $fieldRules[] = 'max:' . $maxPhotos;
                    } else {
                        // Accept both string (old format) and array (new format with GPS)
                        $fieldRules[] = 'nullable';
                    }
                    break;
            }

            $rules["fields.{$field->field_code}"] = $fieldRules;
        }

        $validated = $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            // Create submission with appropriate status
            $submission = new FormSubmission();
            $submission->form_version_id = $version->id;
            $submission->submitted_by = auth()->id();
            
            if ($isDraft) {
                $submission->status = FormSubmission::STATUS_DRAFT;
                $submission->submitted_at = null; // No submitted_at for drafts
            } else {
                $submission->status = 'submitted';
                $submission->submitted_at = now();
            }
            
            $submission->submission_code = $submission->generateSubmissionCode();
            $submission->save();

            // Save answers
            foreach ($regularFields as $field) {
                $value = $validated['fields'][$field->field_code] ?? null;
                
                // For drafts, save even empty values (user might be in progress)
                if ($value === null && !$isDraft && !$field->is_required) {
                    continue;
                }

                // Handle different field types (same as before)
                if ($field->field_type === 'file') {
                    $this->handleFileUpload($field, $request, $submission);
                } elseif (is_array($value)) {
                    $this->saveArrayAnswer($field, $submission, $value);
                } elseif ($field->field_type === 'boolean') {
                    $this->saveBooleanAnswer($field, $submission, $value);
                } elseif ($field->field_type === 'signature') {
                    $signatureData = $validated['fields'][$field->field_code] ?? null;
    
                    if ($signatureData && str_starts_with($signatureData, 'data:image')) {
                        // Convert base64 to file
                        $imageData = explode(',', $signatureData)[1];
                        $decodedImage = base64_decode($imageData);
                        
                        // Generate filename
                        $filename = 'signature_' . $field->field_code . '_' . time() . '.png';
                        
                        // Create folder path
                        $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id . '/signatures';
                        
                        // Store to sigap disk
                        $filePath = $folderPath . '/' . $filename;
                        Storage::disk('s3')->put($filePath, $decodedImage, 'public');
                        
                        $answer = new FormAnswer();
                        $answer->form_submission_id = $submission->id;
                        $answer->form_field_id = $field->id;
                        $answer->answer_value = $filePath;
                        $answer->answer_metadata = [
                            'signature' => true,
                            'filename' => $filename,
                            'width' => $field->validation_rules['width'] ?? 400,
                            'height' => $field->validation_rules['height'] ?? 200,
                            'pen_color' => $field->validation_rules['pen_color'] ?? '#000000',
                            'signed_at' => now()->toISOString(),
                            'signed_by' => auth()->user()->name,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent()
                        ];
                        $answer->save();
                    }
                } elseif ($field->field_type === 'live_photo') {
                    $photoData = $validated['fields'][$field->field_code] ?? null;
                    
                    
                    if ($photoData) {
                        // Handle single photo or multiple photos
                        if (is_string($photoData)) {
                            // Check if it's a JSON string (array or object)
                            if (str_starts_with($photoData, '[') || str_starts_with($photoData, '{')) {
                                $decoded = json_decode($photoData, true);
                                if (is_array($decoded)) {
                                    // Check if it's an associative array (object) or indexed array
                                    if (array_keys($decoded) !== range(0, count($decoded) - 1)) {
                                        // It's an associative array (object) - treat as single photo
                                        $photos = [$decoded];
                                    } else {
                                        // It's an indexed array - multiple photos
                                        $photos = $decoded;
                                    }
                                } else {
                                    $photos = [$decoded];
                                }
                            } else {
                                // Old format: direct base64 string
                                $photos = [$photoData];
                            }
                        } else {
                            // Already an array
                            $photos = $photoData;
                        }
                        
                        $storedPhotos = [];
                        
                        foreach ($photos as $index => $photoItem) {
                            
                            // Handle new format with GPS data
                            $photoBase64 = null;
                            $gpsData = null;
                            
                            if (is_string($photoItem)) {
                                // Old format: direct base64 string
                                $photoBase64 = $photoItem;
                            } elseif (is_array($photoItem) && isset($photoItem['image'])) {
                                // New format: object with image and GPS
                                $photoBase64 = $photoItem['image'];
                                $gpsData = $photoItem['gps'] ?? null;
                            } else {
                            }
                            
                            if ($photoBase64 && is_string($photoBase64) && str_starts_with($photoBase64, 'data:image')) {
                                try {
                                    // Convert base64 to file
                                    $imageData = explode(',', $photoBase64)[1];
                                    $decodedImage = base64_decode($imageData);
                                    
                                    // Generate filename
                                    $filename = 'live_photo_' . $field->field_code . '_' . time() . '_' . $index . '.jpg';
                                    
                                    // Create folder path
                                    $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id . '/live_photos';
                                    
                                    // Store to sigap disk
                                    $filePath = $folderPath . '/' . $filename;
                                    Storage::disk('s3')->put($filePath, $decodedImage, 'public');
                                    
                                    // Add watermark with EXIF data and GPS coordinates
                                    $watermarkedPath = $this->addLivePhotoWatermark($filePath, $decodedImage, $field, $submission, $gpsData);
                                    
                                    $storedPhotos[] = [
                                        'file_path' => $watermarkedPath,
                                        'original_filename' => $filename,
                                        'file_size' => Storage::disk('s3')->size($watermarkedPath),
                                        'captured_at' => now()->toISOString(),
                                        'camera_type' => 'rear',
                                        'photo_quality' => $field->validation_rules['photo_quality'] ?? 0.8,
                                        'user_name' => auth()->user()->name,
                                        'user_id' => auth()->user()->id
                                    ];
                                } catch (\Exception $e) {
                                    // Continue processing other photos
                                }
                            } else {
                            }
                        }
                        
                        if (!empty($storedPhotos)) {
                            $answer = new FormAnswer();
                            $answer->form_submission_id = $submission->id;
                            $answer->form_field_id = $field->id;
                            
                            // Store only file paths in answer_value to keep it small
                            $filePaths = array_column($storedPhotos, 'file_path');
                            $answerValue = count($filePaths) === 1 ? $filePaths[0] : json_encode($filePaths);
                            
                            $answer->answer_value = $answerValue;
                            
                            $answer->answer_metadata = [
                                'live_photo' => true,
                                'photos' => $storedPhotos,
                                'total_photos' => count($storedPhotos),
                                'camera_forced' => 'rear',
                                'captured_at' => now()->toISOString(),
                                'watermarked' => true
                            ];
                            $answer->save();
                        } else {
                        }
                    }
                    // Note: We don't call saveRegularAnswer for live_photo fields to prevent storing base64 data
                } else {
                    $this->saveRegularAnswer($field, $submission, $value);
                }
            }
            
            // Calculate and save calculated fields
            $this->processCalculatedFields($submission);

            // Process hidden fields
            $this->hiddenFieldService->processHiddenFields($submission);

            DB::commit();

            // Only start approval workflow if NOT a draft
            
            if (!$isDraft && $submission->status !== FormSubmission::STATUS_DRAFT) {    
                Log::info("Attempting to start approval workflow for submission {$submission->submission_code}");
                $workflowStarted = $this->approvalService->startApprovalWorkflow($submission);
                
                if (!$workflowStarted) {
                    Log::warning("Approval workflow failed to start for submission {$submission->submission_code}");
                } else {
                    Log::info("Approval workflow started successfully for submission {$submission->submission_code}");
                }
                
                $message = 'Form submitted successfully. Submission Code: ' . $submission->submission_code;
            } else {
                $message = 'Draft saved successfully. Submission Code: ' . $submission->submission_code;
            }

            return redirect()->route('formsubmissions.show', $submission)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded files if transaction fails
            if (isset($submission) && $submission->id) {
                $this->cleanupUploadedFiles($submission);
            }

            Log::error('Form submission failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to ' . ($isDraft ? 'save draft' : 'submit form') . '. Please try again.');
        }
    }

    /**
     * Display submissions with unified filters
     */
    public function submissions(Request $request)
    {
        $user = auth()->user();
        $hasFullAccess = $user->hasAnyRole(['Super Admin', 'Owner']);
        
        // Start building query
        $query = FormSubmission::with(['formVersion.form', 'submitter']);
        
        // Determine filter mode (default to 'my' for regular users, 'all' for admins)
        $filterMode = $request->get('filter_mode', 'my');
        
        // Apply access control based on filter mode
        if ($filterMode === 'my') {
            // Show only user's own submissions
            $query->where('submitted_by', $user->id);
        } elseif ($filterMode === 'department') {
            // Show department submissions
            if (!$hasFullAccess) {
                $userDepartmentIds = $user->departments->pluck('id')->toArray();
                $query->whereHas('submitter.departments', function($q) use ($userDepartmentIds) {
                    $q->whereIn('departments.id', $userDepartmentIds);
                });
            }
            // If has full access, show all (no additional filter needed)
        } elseif ($filterMode === 'all') {
            // Show all accessible submissions
            if (!$hasFullAccess) {
                $userDepartmentIds = $user->departments->pluck('id')->toArray();
                $query->where(function($q) use ($user, $userDepartmentIds) {
                    $q->where('submitted_by', $user->id)
                    ->orWhereHas('submitter.departments', function($q2) use ($userDepartmentIds) {
                        $q2->whereIn('departments.id', $userDepartmentIds);
                    });
                });
            }
            // If has full access, show all (no additional filter needed)
        }
        
        // Apply other filters
        
        // Filter by submission code
        if ($request->filled('submission_code')) {
            $query->where('submission_code', 'LIKE', '%' . $request->submission_code . '%');
        }
        
        // Filter by form
        if ($request->filled('form_id')) {
            $query->whereHas('formVersion.form', function($q) use ($request) {
                $q->where('id', $request->form_id);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by submitter (only if not in 'my' mode)
        if ($request->filled('submitter_id') && $filterMode !== 'my') {
            $query->where('submitted_by', $request->submitter_id);
        }
        
        // Filter by date range (default to last 7 days)
        $dateFrom = $request->filled('date_from') 
            ? $request->date_from 
            : now()->timezone('Asia/Jakarta')->subDays(7)->format('Y-m-d');
        
        $dateTo = $request->filled('date_to') 
            ? $request->date_to 
            : now()->timezone('Asia/Jakarta')->format('Y-m-d');
        
        // Only apply date filter if dates are provided or it's the initial load
        if ($request->filled('date_from') || $request->filled('date_to') || !$request->has('filter_mode')) {
            $query->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);
        }
        
        // Get data for dropdowns
        
        // Get all forms that have submissions
        $availableForms = Form::whereHas('versions.submissions')
            ->orderBy('name')
            ->get();
        
        // Get submitters based on access and filter mode
        if ($hasFullAccess) {
            $availableSubmitters = User::where('active', 1)->whereHas('submissions')
                ->orderBy('name')
                ->get();
        } else {
            $userDepartmentIds = $user->departments->pluck('id')->toArray();
            $availableSubmitters = User::where('active', 1)->whereHas('submissions')
                ->where(function($q) use ($user, $userDepartmentIds) {
                    $q->where('id', $user->id)
                    ->orWhereHas('departments', function($q2) use ($userDepartmentIds) {
                        $q2->whereIn('departments.id', $userDepartmentIds);
                    });
                })
                ->orderBy('name')
                ->get();
        }
        
        // Clone query for statistics before pagination
        $statsQuery = clone $query;
        
        // Order and paginate
        $submissions = $query->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Statistics
        $stats = [
            'total' => $statsQuery->count(),
            'today' => (clone $statsQuery)->whereDate('created_at', today())->count(),
            'pending' => (clone $statsQuery)->whereIn('status', ['submitted', 'under_review'])->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
        ];

        return view('formsubmissions.submissions', compact(
            'submissions', 
            'availableForms', 
            'availableSubmitters',
            'hasFullAccess',
            'filterMode',
            'dateFrom',
            'dateTo',
            'stats'
        ));
    }

    /**
     * Show submission details
     */
    public function show(FormSubmission $submission)
    {
        $user = auth()->user();
    
        // Super Admin/Owner can view all submissions
        if ($user->hasAnyRole(['Super Admin', 'Owner'])) {
            // Full access - can view any submission
        } elseif ($submission->submitted_by != $user->id) {
            // Check if user is in same department as submitter
            $submitterDeptIds = $submission->submitter->departments->pluck('id');
            $viewerDeptIds = $user->departments->pluck('id');
            
            if ($submitterDeptIds->intersect($viewerDeptIds)->isEmpty()) {
                abort(403, 'You do not have permission to view this submission.');
            }
        }

        $submission->load([
            'formVersion.form',
            'answers.field',
            'submitter',
            'approvalHistory.step',
            'approvalHistory.approver',
            'approvalHistory.assignedUser'
        ]);

         // Get approval summary
        $approvalSummary = null;
        $canApprove = false;
        $pendingApproval = null;

        if ($submission->needsApproval()) {
            $approvalSummary = $this->approvalService->getApprovalSummary($submission);
            $canApprove = $this->approvalService->canUserApprove($submission, auth()->user());
            
            if ($canApprove) {
                $pendingApproval = $submission->approvalLogs()
                    ->where('assigned_to', auth()->id())
                    ->where('status', ApprovalLog::STATUS_PENDING)
                    ->first();
            }
        }

        return view('formsubmissions.show', compact(
            'submission', 
            'approvalSummary', 
            'canApprove', 
            'pendingApproval'
        ));
    }

    /**
     * Edit draft submission (if allowed)
     */
    public function edit(FormSubmission $submission)
    {
        // Check if submission is draft and belongs to user
        if ($submission->status !== 'draft' || $submission->submitted_by != auth()->id()) {
            return redirect()->route('formsubmissions.show', $submission)
                ->with('error', 'You cannot edit this submission.');
        }

        $form = $submission->formVersion->form;
        $version = $submission->formVersion;
        $fields = $version->fields()
            ->with('options')
            ->ordered()
            ->get();

        // Get existing answers
        $existingAnswers = [];
        foreach ($submission->answers as $answer) {
            $value = $answer->answer_value;
            
            // Decode JSON for array values
            if ($answer->field->field_type === 'select_multiple' || 
                $answer->field->field_type === 'checkbox') {
                $value = json_decode($value, true);
            }
            
            $existingAnswers[$answer->field->field_code] = $value;
        }

        return view('formsubmissions.edit', compact('submission', 'form', 'version', 'fields', 'existingAnswers'));
    }

    /**
     * Update draft submission
     */
    public function update(Request $request, FormSubmission $submission)
    {
        // Check if submission is draft and belongs to user
        if ($submission->status !== 'draft' || $submission->submitted_by != auth()->id()) {
            return redirect()->route('formsubmissions.show', $submission)
                ->with('error', 'You cannot edit this submission.');
        }

        // Get form version and fields
        $version = $submission->formVersion;
        $fields = $version->fields;
        $regularFields = $fields->where('field_type', '!=', 'calculated');

        // Build validation rules based on fields
        $rules = [];
        $messages = [];

        foreach ($regularFields as $field) {
            $fieldRules = [];
            
            // Required validation
            if ($field->is_required) {
                // For file fields, check if already has answer
                if ($field->field_type === 'file') {
                    $existingAnswer = $submission->answers()->where('form_field_id', $field->id)->first();
                    if (!$existingAnswer || !$existingAnswer->answer_value) {
                        $fieldRules[] = 'required';
                        $messages["fields.{$field->field_code}.required"] = "{$field->field_label} is required.";
                    } else {
                        $fieldRules[] = 'nullable';
                    }
                } else {
                    $fieldRules[] = 'required';
                    $messages["fields.{$field->field_code}.required"] = "{$field->field_label} is required.";
                }
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-specific validation
            switch ($field->field_type) {
                case 'number':
                    $fieldRules[] = 'integer';
                    break;
                case 'decimal':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240'; // 10MB max
                    break;
                case 'select_single':
                case 'radio':
                    if ($field->options->count() > 0) {
                        $fieldRules[] = 'in:' . $field->options->pluck('option_value')->implode(',');
                    }
                    break;
                case 'select_multiple':
                case 'checkbox':
                    $fieldRules[] = 'array';
                    if ($field->options->count() > 0) {
                        $fieldRules[] = 'in:' . $field->options->pluck('option_value')->implode(',');
                    }
                    break;
                case 'boolean':
                    $fieldRules[] = 'boolean';
                    break;
                case 'live_photo':
                    $photoRules = $field->validation_rules ?? [];
                    $maxPhotos = $photoRules['max_photos'] ?? 1;
                    
                    if ($maxPhotos > 1) {
                        $fieldRules[] = 'array';
                        $fieldRules[] = 'max:' . $maxPhotos;
                    } else {
                        // Accept both string (old format) and array (new format with GPS)
                        $fieldRules[] = 'nullable';
                    }
                    break;
            }

            $rules["fields.{$field->field_code}"] = $fieldRules;
        }

        $validated = $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            // Update submission status based on action
            $action = $request->input('action');
            $isSubmitting = $action === 'submit';
            $isDraft = $action === 'save_draft';
            
            
            if ($isSubmitting) {
                $submission->status = 'submitted';
                $submission->submitted_at = now();
            } elseif ($isDraft) {
                // Keep as draft - don't change status or submitted_at
            }
            
            $submission->save();

            // Update answers
            foreach ($regularFields as $field) {
                $value = $validated['fields'][$field->field_code] ?? null;
                
                // Get existing answer
                $answer = $submission->answers()->where('form_field_id', $field->id)->first();

                // Handle different field types
                if ($field->field_type === 'file') {
                    $this->updateFileAnswer($field, $request, $submission, $answer);
                } elseif (is_array($value)) {
                    $this->updateArrayAnswer($field, $submission, $answer, $value);
                } elseif ($field->field_type === 'boolean') {
                    $this->updateBooleanAnswer($field, $submission, $answer, $value);
                } elseif ($field->field_type === 'signature') {
                    $signatureData = $validated['fields'][$field->field_code] ?? null;
    
                    // Get existing answer
                    $answer = $submission->answers()->where('form_field_id', $field->id)->first();
                    
                    if ($signatureData && str_starts_with($signatureData, 'data:image')) {
                        // New signature provided - delete old file if exists
                        if ($answer && $answer->answer_value) {
                            if (Storage::disk('s3')->exists($answer->answer_value)) {
                                Storage::disk('s3')->delete($answer->answer_value);
                            }
                        }
                        
                        // Convert base64 to file
                        $imageData = explode(',', $signatureData)[1];
                        $decodedImage = base64_decode($imageData);
                        
                        // Generate filename
                        $filename = 'signature_' . $field->field_code . '_' . time() . '.png';
                        
                        // Create folder path
                        $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id . '/signatures';
                        
                        // Store to sigap disk
                        $filePath = $folderPath . '/' . $filename;
                        Storage::disk('s3')->put($filePath, $decodedImage, 'public');
                        
                        $signatureMetadata = [
                            'signature' => true,
                            'filename' => $filename,
                            'width' => $field->validation_rules['width'] ?? 400,
                            'height' => $field->validation_rules['height'] ?? 200,
                            'pen_color' => $field->validation_rules['pen_color'] ?? '#000000',
                            'signed_at' => now()->toISOString(),
                            'signed_by' => auth()->user()->name,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'replaced' => $answer ? true : false,
                            'replaced_at' => $answer ? now()->toISOString() : null
                        ];
                        
                        if ($answer) {
                            $answer->update([
                                'answer_value' => $filePath,
                                'answer_metadata' => $signatureMetadata
                            ]);
                        } else {
                            FormAnswer::create([
                                'form_submission_id' => $submission->id,
                                'form_field_id' => $field->id,
                                'answer_value' => $filePath,
                                'answer_metadata' => $signatureMetadata
                            ]);
                        }
                    }
                } elseif ($field->field_type === 'live_photo') {
                    $photoData = $validated['fields'][$field->field_code] ?? null;
                    
                    // Get existing answer
                    $answer = $submission->answers()->where('form_field_id', $field->id)->first();
                    
                    if ($photoData) {
                        // Delete old photos if they exist
                        if ($answer && $answer->answer_value) {
                            $this->deleteLivePhotos($answer);
                        }
                        
                        // Handle single photo or multiple photos
                        if (is_string($photoData)) {
                            // Check if it's a JSON string (array or object)
                            if (str_starts_with($photoData, '[') || str_starts_with($photoData, '{')) {
                                $decoded = json_decode($photoData, true);
                                if (is_array($decoded)) {
                                    // Check if it's an associative array (object) or indexed array
                                    if (array_keys($decoded) !== range(0, count($decoded) - 1)) {
                                        // It's an associative array (object) - treat as single photo
                                        $photos = [$decoded];
                                    } else {
                                        // It's an indexed array - multiple photos
                                        $photos = $decoded;
                                    }
                                } else {
                                    $photos = [$decoded];
                                }
                            } else {
                                // Old format: direct base64 string
                                $photos = [$photoData];
                            }
                        } else {
                            // Already an array
                            $photos = $photoData;
                        }
                        
                        $storedPhotos = [];
                        
                        foreach ($photos as $index => $photoItem) {
                            
                            // Handle new format with GPS data
                            $photoBase64 = null;
                            $gpsData = null;
                            
                            if (is_string($photoItem)) {
                                // Old format: direct base64 string
                                $photoBase64 = $photoItem;
                            } elseif (is_array($photoItem) && isset($photoItem['image'])) {
                                // New format: object with image and GPS
                                $photoBase64 = $photoItem['image'];
                                $gpsData = $photoItem['gps'] ?? null;
                            } else {
                            }
                            
                            if ($photoBase64 && is_string($photoBase64) && str_starts_with($photoBase64, 'data:image')) {
                                try {
                                    // Convert base64 to file
                                    $imageData = explode(',', $photoBase64)[1];
                                    $decodedImage = base64_decode($imageData);
                                    
                                    // Generate filename
                                    $filename = 'live_photo_' . $field->field_code . '_' . time() . '_' . $index . '.jpg';
                                    
                                    // Create folder path
                                    $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id . '/live_photos';
                                    
                                    // Store to sigap disk
                                    $filePath = $folderPath . '/' . $filename;
                                    Storage::disk('s3')->put($filePath, $decodedImage, 'public');
                                    
                                    // Add watermark with EXIF data and GPS coordinates
                                    $watermarkedPath = $this->addLivePhotoWatermark($filePath, $decodedImage, $field, $submission, $gpsData);
                                    
                                    $storedPhotos[] = [
                                        'file_path' => $watermarkedPath,
                                        'original_filename' => $filename,
                                        'file_size' => Storage::disk('s3')->size($watermarkedPath),
                                        'captured_at' => now()->toISOString(),
                                        'camera_type' => 'rear',
                                        'photo_quality' => $field->validation_rules['photo_quality'] ?? 0.8,
                                        'user_name' => auth()->user()->name,
                                        'user_id' => auth()->user()->id,
                                        'replaced' => $answer ? true : false,
                                        'replaced_at' => $answer ? now()->toISOString() : null
                                    ];
                                } catch (\Exception $e) {
                                    // Continue processing other photos
                                }
                            } else {
                            }
                        }
                        
                        if (!empty($storedPhotos)) {
                            $livePhotoMetadata = [
                                'live_photo' => true,
                                'photos' => $storedPhotos,
                                'total_photos' => count($storedPhotos),
                                'camera_forced' => 'rear',
                                'captured_at' => now()->toISOString(),
                                'watermarked' => true
                            ];
                            
                            // Store only file paths in answer_value to keep it small
                            $filePaths = array_column($storedPhotos, 'file_path');
                            $answerValue = count($filePaths) === 1 ? $filePaths[0] : json_encode($filePaths);
                            
                            if ($answer) {
                                $answer->update([
                                    'answer_value' => $answerValue,
                                    'answer_metadata' => $livePhotoMetadata
                                ]);
                            } else {
                                FormAnswer::create([
                                    'form_submission_id' => $submission->id,
                                    'form_field_id' => $field->id,
                                    'answer_value' => $answerValue,
                                    'answer_metadata' => $livePhotoMetadata
                                ]);
                            }
                        } else {
                        }
                    }
                    // Note: We don't call updateRegularAnswer for live_photo fields to prevent storing base64 data
                } else {
                    $this->updateRegularAnswer($field, $submission, $answer, $value);
                }
            }

            // Recalculate all calculated fields after updating regular fields
            $this->recalculateFieldsForSubmission($submission);

            // Reprocess hidden fields (in case dynamic values changed)
            $this->hiddenFieldService->processHiddenFields($submission);

            DB::commit();

            // Start approval workflow if submitting
            
            if ($isSubmitting && $submission->status !== FormSubmission::STATUS_DRAFT) {
                Log::info("Attempting to start approval workflow for submission {$submission->submission_code} (update)");
                $workflowStarted = $this->approvalService->startApprovalWorkflow($submission);
                
                if (!$workflowStarted) {
                    Log::warning("Approval workflow failed to start for submission {$submission->submission_code}");
                } else {
                    Log::info("Approval workflow started successfully for submission {$submission->submission_code} (update)");
                }
            }

            // Redirect based on action
            if ($isSubmitting) {
                return redirect()->route('formsubmissions.show', $submission)
                    ->with('success', 'Form submitted successfully and sent for approval.');
            } else {
                return redirect()->route('formsubmissions.edit', $submission)
                    ->with('success', 'Draft saved successfully.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update submission. Please try again.');
        }
    }

    /**
     * Delete draft submission
     */
    public function destroy(FormSubmission $submission)
    {
        // Check if submission is draft and belongs to user
        if ($submission->status !== 'draft' || $submission->submitted_by != auth()->id()) {
            return back()->with('error', 'You cannot delete this submission.');
        }

        // Delete uploaded files from sigap disk
        $fileAnswers = $submission->answers()
            ->whereHas('field', function($query) {
                $query->whereIn('field_type', ['file', 'live_photo', 'signature']);
            })
            ->get();
        
        foreach ($fileAnswers as $answer) {
            if ($answer->field->field_type === 'live_photo') {
                $this->deleteLivePhotos($answer);
            } elseif ($answer->answer_value && Storage::disk('s3')->exists($answer->answer_value)) {
                Storage::disk('s3')->delete($answer->answer_value);
            }
        }

        $submission->delete();

        return redirect()->route('formsubmissions.my-submissions')
            ->with('success', 'Draft submission deleted successfully.');
    }

    /**
     * Print submission
     */
    public function print(FormSubmission $submission)
    {
        // Check view permission (same as show method)
        if ($submission->submitted_by != auth()->id() && !auth()->user()->hasRole('admin')) {
            $submitterDeptIds = $submission->submitter->departments->pluck('id');
            $viewerDeptIds = auth()->user()->departments->pluck('id');
            
            if ($submitterDeptIds->intersect($viewerDeptIds)->isEmpty()) {
                abort(403);
            }
        }

        $submission->load([
            'formVersion.form',
            'answers.field',
            'submitter'
        ]);

        return view('formsubmissions.print', compact('submission'));
    }

    /**
     * Clean up uploaded files if submission fails
     */
    private function cleanupUploadedFiles(FormSubmission $submission)
    {
        $fileAnswers = $submission->answers()
            ->whereHas('field', function($query) {
                $query->whereIn('field_type', ['file', 'live_photo', 'signature']);
            })
            ->get();
        
        foreach ($fileAnswers as $answer) {
            if ($answer->field->field_type === 'live_photo') {
                $this->deleteLivePhotos($answer);
            } elseif ($answer->answer_value && Storage::disk('s3')->exists($answer->answer_value)) {
                Storage::disk('s3')->delete($answer->answer_value);
            }
        }
    }

    // Helper method to validate file
    private function validateFile($file, $rules)
    {
        $allowedExtensions = $rules['allowed_extensions'] ?? [];
        $maxSize = ($rules['max_file_size'] ?? 10240) * 1024; // Convert KB to bytes
        
        // Check extension
        if (!empty($allowedExtensions)) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                return false;
            }
        }
        
        // Check size
        if ($file->getSize() > $maxSize) {
            return false;
        }
        
        return true;
    }

    /**
     * Process calculated fields for submission
     */
    private function processCalculatedFields(FormSubmission $submission)
    {
        $calculatedFields = $submission->formVersion->fields()
            ->where('field_type', 'calculated')
            ->orderBy('order_position') // Calculate in order
            ->get();
        
        $calculationService = app(\App\Services\CalculationService::class);
        
        foreach ($calculatedFields as $field) {
            $calculatedValue = $calculationService->calculateFieldValue($field, $submission);
            
            if ($calculatedValue !== null) {
                FormAnswer::create([
                    'form_submission_id' => $submission->id,
                    'form_field_id' => $field->id,
                    'answer_value' => $calculatedValue,
                    'answer_metadata' => [
                        'calculated' => true,
                        'formula' => $field->calculation_formula,
                        'calculated_at' => now()->toISOString()
                    ]
                ]);
            }
        }
    }

    /**
     * Helper methods for different field types
     */
    private function handleFileUpload(FormField $field, Request $request, FormSubmission $submission)
    {
        $fileRules = $field->validation_rules ?? [];
        $allowMultiple = $fileRules['allow_multiple'] ?? false;
        
        if ($allowMultiple && $request->hasFile("fields.{$field->field_code}")) {
            // Multiple files handling (same as before)
            $files = $request->file("fields.{$field->field_code}");
            $uploadedFiles = [];
            
            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                //$filename = Str::slug($filenameWithoutExt) . '_' . time() . '_' . uniqid() . '.' . $extension;
                $filename = $file->hashName() . '.' . $extension;
                
                $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id;
                
                $path = $file->storeAs($folderPath, $filename, [
                    'disk' => 's3',
                    'visibility' => 'public'
                ]);
                
                $uploadedFiles[] = [
                    'path' => $path,
                    'original_name' => $originalName,
                    'filename' => $filename,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $extension
                ];
            }
            
            if (!empty($uploadedFiles)) {
                FormAnswer::create([
                    'form_submission_id' => $submission->id,
                    'form_field_id' => $field->id,
                    'answer_value' => json_encode(array_column($uploadedFiles, 'path')),
                    'answer_metadata' => [
                        'files' => $uploadedFiles,
                        'count' => count($uploadedFiles),
                        'disk' => 's3',
                        'multiple' => true
                    ]
                ]);
            }
        } elseif (!$allowMultiple && $request->hasFile("fields.{$field->field_code}")) {
            // Single file handling (same as before)
            $file = $request->file("fields.{$field->field_code}");
            
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            //$filename = Str::slug($filenameWithoutExt) . '_' . time() . '.' . $extension;
            $filename = $file->hashName() . '.' . $extension;
            
            $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id;
            
            $path = $file->storeAs($folderPath, $filename, [
                'disk' => 's3',
                'visibility' => 'public'
            ]);
            
            FormAnswer::create([
                'form_submission_id' => $submission->id,
                'form_field_id' => $field->id,
                'answer_value' => $path,
                'answer_metadata' => [
                    'original_name' => $originalName,
                    'filename' => $filename,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $extension,
                    'disk' => 'sigap',
                    'multiple' => false
                ]
            ]);
        }
    }

    private function saveArrayAnswer(FormField $field, FormSubmission $submission, array $value)
    {
        FormAnswer::create([
            'form_submission_id' => $submission->id,
            'form_field_id' => $field->id,
            'answer_value' => json_encode($value)
        ]);
    }

    private function saveBooleanAnswer(FormField $field, FormSubmission $submission, $value)
    {
        FormAnswer::create([
            'form_submission_id' => $submission->id,
            'form_field_id' => $field->id,
            'answer_value' => $value ? '1' : '0'
        ]);
    }

    private function saveRegularAnswer(FormField $field, FormSubmission $submission, $value)
    {
        FormAnswer::create([
            'form_submission_id' => $submission->id,
            'form_field_id' => $field->id,
            'answer_value' => $value
        ]);
    }

    /**
     * Recalculate calculated fields for submission
     */
    private function recalculateFieldsForSubmission(FormSubmission $submission)
    {
        $calculatedFields = $submission->formVersion->fields()
            ->where('field_type', 'calculated')
            ->orderBy('order_position') // Calculate in order in case one calculated field depends on another
            ->get();
        
        $calculationService = app(\App\Services\CalculationService::class);
        
        foreach ($calculatedFields as $field) {
            $calculatedValue = $calculationService->calculateFieldValue($field, $submission);
            
            if ($calculatedValue !== null) {
                // Find existing answer
                $answer = $submission->answers()->where('form_field_id', $field->id)->first();
                
                if ($answer) {
                    $answer->update([
                        'answer_value' => $calculatedValue,
                        'answer_metadata' => [
                            'calculated' => true,
                            'formula' => $field->calculation_formula,
                            'calculated_at' => now()->toISOString(),
                            'updated_at' => now()->toISOString()
                        ]
                    ]);
                } else {
                    FormAnswer::create([
                        'form_submission_id' => $submission->id,
                        'form_field_id' => $field->id,
                        'answer_value' => $calculatedValue,
                        'answer_metadata' => [
                            'calculated' => true,
                            'formula' => $field->calculation_formula,
                            'calculated_at' => now()->toISOString()
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * Helper methods for updating different field types
     */
    private function updateFileAnswer(FormField $field, Request $request, FormSubmission $submission, $answer)
    {
        $fileRules = $field->validation_rules ?? [];
        $allowMultiple = $fileRules['allow_multiple'] ?? false;
        
        if ($allowMultiple && $request->hasFile("fields.{$field->field_code}")) {
            // Delete old files
            if ($answer && $answer->answer_value) {
                $oldPaths = json_decode($answer->answer_value, true);
                if (is_array($oldPaths)) {
                    foreach ($oldPaths as $path) {
                        if (Storage::disk('s3')->exists($path)) {
                            Storage::disk('s3')->delete($path);
                        }
                    }
                }
            }
            
            // Upload new files (same logic as store)
            $files = $request->file("fields.{$field->field_code}");
            $uploadedFiles = [];
            
            foreach ($files as $file) {
                // Same file upload logic as store method
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                //$filename = Str::slug($filenameWithoutExt) . '_' . time() . '_' . uniqid() . '.' . $extension;
                $filename = $file->hashName() . '.' . $extension;
                
                $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id;
                
                $path = $file->storeAs($folderPath, $filename, [
                    'disk' => 's3',
                    'visibility' => 'public'
                ]);
                
                $uploadedFiles[] = [
                    'path' => $path,
                    'original_name' => $originalName,
                    'filename' => $filename,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $extension
                ];
            }
            
            if ($answer) {
                $answer->update([
                    'answer_value' => json_encode(array_column($uploadedFiles, 'path')),
                    'answer_metadata' => [
                        'files' => $uploadedFiles,
                        'count' => count($uploadedFiles),
                        'disk' => 's3',
                        'multiple' => true
                    ]
                ]);
            } else {
                FormAnswer::create([
                    'form_submission_id' => $submission->id,
                    'form_field_id' => $field->id,
                    'answer_value' => json_encode(array_column($uploadedFiles, 'path')),
                    'answer_metadata' => [
                        'files' => $uploadedFiles,
                        'count' => count($uploadedFiles),
                        'disk' => 's3',
                        'multiple' => true
                    ]
                ]);
            }
        } elseif (!$allowMultiple && $request->hasFile("fields.{$field->field_code}")) {
            // Single file handling (same as before)
            $file = $request->file("fields.{$field->field_code}");
            
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            //$filename = Str::slug($filenameWithoutExt) . '_' . time() . '.' . $extension;
            $filename = $file->hashName() . '.' . $extension;
            
            $folderPath = 'formsubmissions/' . $submission->formVersion->form_id . '/' . date('Y') . '/' . date('m') . '/' . $submission->id;
            
            $path = $file->storeAs($folderPath, $filename, [
                'disk' => 's3',
                'visibility' => 'public'
            ]);
            
            FormAnswer::create([
                'form_submission_id' => $submission->id,
                'form_field_id' => $field->id,
                'answer_value' => $path,
                'answer_metadata' => [
                    'original_name' => $originalName,
                    'filename' => $filename,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $extension,
                    'disk' => 'sigap',
                    'multiple' => false
                ]
            ]);
        }
    }

    private function updateArrayAnswer(FormField $field, FormSubmission $submission, $answer, array $value)
    {
        $jsonValue = json_encode($value);
        
        if ($answer) {
            $answer->update(['answer_value' => $jsonValue]);
        } else {
            FormAnswer::create([
                'form_submission_id' => $submission->id,
                'form_field_id' => $field->id,
                'answer_value' => $jsonValue
            ]);
        }
    }

    private function updateBooleanAnswer(FormField $field, FormSubmission $submission, $answer, $value)
    {
        $boolValue = $value ? '1' : '0';
        
        if ($answer) {
            $answer->update(['answer_value' => $boolValue]);
        } else {
            FormAnswer::create([
                'form_submission_id' => $submission->id,
                'form_field_id' => $field->id,
                'answer_value' => $boolValue
            ]);
        }
    }

    private function updateRegularAnswer(FormField $field, FormSubmission $submission, $answer, $value)
    {
        if ($value === null && !$field->is_required) {
            // Delete existing answer if any
            if ($answer) {
                $answer->delete();
            }
            return;
        }
        
        if ($answer) {
            $answer->update(['answer_value' => $value]);
        } else {
            FormAnswer::create([
                'form_submission_id' => $submission->id,
                'form_field_id' => $field->id,
                'answer_value' => $value
            ]);
        }
    }

    /**
     * Process approval action
     */
    public function processApproval(Request $request, FormSubmission $submission)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000'
        ]);

        try {
            // Find user's pending approval
            $pendingApproval = $submission->approvalLogs()
                ->where('assigned_to', auth()->id())
                ->where('status', ApprovalLog::STATUS_PENDING)
                ->first();

            if (!$pendingApproval) {
                return back()->with('error', 'You do not have a pending approval for this submission.');
            }

            // Process the approval
            $action = $validated['action'] === 'approve' 
                ? ApprovalLog::STATUS_APPROVED 
                : ApprovalLog::STATUS_REJECTED;

            $this->approvalService->processApproval(
                $pendingApproval, 
                auth()->user(), 
                $action, 
                $validated['comments']
            );

            $actionText = $validated['action'] === 'approve' ? 'approved' : 'rejected';
            
            return redirect()->route('formsubmissions.show', $submission)
                ->with('success', "Submission {$actionText} successfully.");

        } catch (\Exception $e) {
            Log::error('Approval processing failed: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Failed to process approval: ' . $e->getMessage());
        }
    }

    /**
     * Get pending approvals for current user
     */
    public function pendingApprovals(Request $request)
    {
        $user = auth()->user();
        
        $pendingApprovals = $this->approvalService->getPendingApprovalsForUser($user);
        
        // Group by form type for better display
        $groupedApprovals = $pendingApprovals->groupBy(function($approval) {
            return $approval->submission->formVersion->form->name;
        });

        // Get statistics
        $stats = [
            'total_pending' => $pendingApprovals->count(),
            'overdue' => $pendingApprovals->filter(function($approval) {
                return $approval->isOverdue();
            })->count(),
            'due_today' => $pendingApprovals->filter(function($approval) {
                return $approval->due_at && $approval->due_at->isToday();
            })->count(),
            'urgent' => $pendingApprovals->filter(function($approval) {
                return $approval->due_at && $approval->due_at->diffInHours(now()) <= 4;
            })->count()
        ];

        return view('formsubmissions.pending-approvals', compact('groupedApprovals', 'stats'));
    }

    /**
     * Show approval history for submission
     */
    public function approvalHistory(FormSubmission $submission)
    {
        // Check permission
        if (!$this->userCanViewSubmission($submission)) {
            abort(403, 'You do not have permission to view this submission.');
        }

        $submission->load([
            'formVersion.form',
            'approvalHistory.step',
            'approvalHistory.approver',
            'approvalHistory.assignedUser'
        ]);

        $approvalSummary = $this->approvalService->getApprovalSummary($submission);

        return view('formsubmissions.approval-history', compact('submission', 'approvalSummary'));
    }

    /**
     * Check if user can view submission (including approval context)
     */
    private function userCanViewSubmission(FormSubmission $submission): bool
    {
        $user = auth()->user();
        
        // Super Admin/Owner can view all
        if ($user->hasAnyRole(['Super Admin', 'Owner', 'Business Owner'])) {
            return true;
        }
        
        // Submitter can view their own
        if ($submission->submitted_by == $user->id) {
            return true;
        }
        
        // Approvers can view submissions they need to approve or have approved
        if ($submission->approvalLogs()->where('assigned_to', $user->id)->exists()) {
            return true;
        }
        
        // Department access
        $submitterDeptIds = $submission->submitter->departments->pluck('id');
        $viewerDeptIds = $user->departments->pluck('id');
        
        return $submitterDeptIds->intersect($viewerDeptIds)->isNotEmpty();
    }

    public function recheckApproval(FormSubmission $submission)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) {
            abort(403);
        }
        
        try {
            // Force recheck workflow progress
            $reflection = new \ReflectionClass($this->approvalService);
            $method = $reflection->getMethod('checkWorkflowProgress');
            $method->setAccessible(true);
            $method->invoke($this->approvalService, $submission);
            
            return back()->with('success', 'Approval status rechecked successfully.');
            
        } catch (\Exception $e) {
            Log::error('Manual recheck failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to recheck approval status.');
        }
    }

    /**
     * Manually start approval workflow for a submission
     */
    public function startWorkflow(FormSubmission $submission)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) {
            abort(403);
        }
        
        try {
            // Check if submission is in submitted status
            if ($submission->status !== FormSubmission::STATUS_SUBMITTED) {
                return back()->with('error', 'Can only start workflow for submitted forms.');
            }
            
            // Check if workflow already started
            if ($submission->approvalLogs()->exists()) {
                return back()->with('error', 'Approval workflow has already been started for this submission.');
            }
            
            Log::info("Manually starting approval workflow for submission {$submission->submission_code}");
            $workflowStarted = $this->approvalService->startApprovalWorkflow($submission);
            
            if (!$workflowStarted) {
                Log::warning("Manual approval workflow failed to start for submission {$submission->submission_code}");
                return back()->with('error', 'Failed to start approval workflow. Check logs for details.');
            }
            
            Log::info("Manual approval workflow started successfully for submission {$submission->submission_code}");
            return back()->with('success', 'Approval workflow started successfully.');
            
        } catch (\Exception $e) {
            Log::error('Manual workflow start failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to start approval workflow: ' . $e->getMessage());
        }
    }

    /**
     * Process URL parameters for form prefill
     */
    private function processPrefillParameters(Request $request, $fields): array
    {
        $prefillData = [];
        
        // Define allowed field types for prefill
        $allowedTypes = [
            'text_short', 'number', 'decimal', 'date', 'datetime',
            'select_single', 'select_multiple', 'radio', 'checkbox', 
            'boolean', 'hidden'
        ];
        
        foreach ($fields as $field) {
            // Only process allowed field types
            if (!in_array($field->field_type, $allowedTypes)) {
                continue;
            }
            
            $fieldCode = $field->field_code;
            
            // Check if URL parameter exists for this field
            if ($request->has($fieldCode)) {
                $paramValue = $request->get($fieldCode);
                $processedValue = $this->validateAndProcessPrefillValue($field, $paramValue);
                
                if ($processedValue !== null) {
                    $prefillData[$fieldCode] = $processedValue;
                }
            }
        }
        
        return $prefillData;
    }

    /**
     * Validate and process prefill value based on field type
     */
    private function validateAndProcessPrefillValue(FormField $field, $paramValue)
    {
        try {
            switch ($field->field_type) {
                case 'text_short':
                    return strlen($paramValue) <= 255 ? $paramValue : substr($paramValue, 0, 255);
                    
                case 'number':
                    return is_numeric($paramValue) && is_int($paramValue + 0) ? (int)$paramValue : null;
                    
                case 'decimal':
                    return is_numeric($paramValue) ? (float)$paramValue : null;
                    
                case 'date':
                    $date = \Carbon\Carbon::createFromFormat('Y-m-d', $paramValue);
                    return $date ? $date->format('Y-m-d') : null;
                    
                case 'datetime':
                    // Support multiple datetime formats
                    $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i'];
                    foreach ($formats as $format) {
                        try {
                            $datetime = \Carbon\Carbon::createFromFormat($format, $paramValue);
                            return $datetime ? $datetime->format('Y-m-d\TH:i') : null;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                    return null;
                    
                case 'select_single':
                case 'radio':
                    // Validate against available options
                    $validValues = $field->options->pluck('option_value')->toArray();
                    return in_array($paramValue, $validValues) ? $paramValue : null;
                    
                case 'select_multiple':
                case 'checkbox':
                    // Support comma-separated values
                    $values = is_array($paramValue) ? $paramValue : explode(',', $paramValue);
                    $validValues = $field->options->pluck('option_value')->toArray();
                    $filteredValues = array_intersect($values, $validValues);
                    return !empty($filteredValues) ? array_values($filteredValues) : null;
                    
                case 'boolean':
                    // Support various boolean representations
                    $trueValues = ['1', 'true', 'yes', 'on'];
                    $falseValues = ['0', 'false', 'no', 'off'];
                    
                    if (in_array(strtolower($paramValue), $trueValues)) {
                        return '1';
                    } elseif (in_array(strtolower($paramValue), $falseValues)) {
                        return '0';
                    }
                    return null;
                    
                case 'hidden':
                    // For hidden fields, just return the value (will be validated by hidden field service)
                    return $paramValue;
                    
                default:
                    return null;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to process prefill value for field ' . $field->field_code . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add watermark to live photo with EXIF data and GPS coordinates
     */
    private function addLivePhotoWatermark($filePath, $imageData, $field, $submission, $gpsData = null)
    {
        
        try {
            // Initialize ImageManager (same as FileController)
            $driver = config('image.driver', 'gd') === 'imagick' 
                ? new \Intervention\Image\Drivers\Imagick\Driver() 
                : new \Intervention\Image\Drivers\Gd\Driver();
                
            $imageManager = new \Intervention\Image\ImageManager($driver);
            
            // Read image from data
            $image = $imageManager->read($imageData);
            
            // Try to read EXIF data
            $exifData = $this->extractExifData($imageData);
            
            // Get user information
            $user = auth()->user();
            $userName = $user->name ?? 'Unknown User';
            
            // Convert time to Asia/Jakarta timezone
            $captureTime = $exifData['date_time'] ?? now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
            if ($exifData['date_time']) {
                try {
                    $captureTime = \Carbon\Carbon::parse($exifData['date_time'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $captureTime = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
                }
            }
            
            // Use GPS data from frontend if available, otherwise try EXIF
            if ($gpsData && isset($gpsData['latitude']) && isset($gpsData['longitude'])) {
                $gpsLocation = $gpsData['latitude'] . ', ' . $gpsData['longitude'];
            } else {
                $gpsLocation = $exifData['gps'] ?? 'Location not available';
            }
            
            // Create watermark text in the requested format
            $watermarkText = "PT. SIAP Live Photo\n";
            $watermarkText .= "Taken by: {$userName}\n";
            $watermarkText .= "Date/Time: {$captureTime}\n";
            $watermarkText .= "Location: {$gpsLocation}";
            
            // Add watermark
            $this->addLivePhotoWatermarkText($image, $watermarkText);
            
            // Save watermarked image
            $watermarkedData = $image->toJpeg(90);
            Storage::disk('s3')->put($filePath, $watermarkedData, 'public');
            
            return $filePath;
            
        } catch (\Exception $e) {
            \Log::error('Live photo watermarking failed: ' . $e->getMessage());
            
            // Return original file if watermarking fails
            Storage::disk('s3')->put($filePath, $imageData, 'public');
            return $filePath;
        }
    }

    /**
     * Extract EXIF data from image
     */
    private function extractExifData($imageData)
    {
        try {
            // Create temporary file to read EXIF
            $tempFile = tempnam(sys_get_temp_dir(), 'exif_');
            file_put_contents($tempFile, $imageData);
            
            $exif = exif_read_data($tempFile);
            unlink($tempFile);
            
            if (!$exif) {
                return [
                    'date_time' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'gps' => 'Location not available'
                ];
            }
            
            // Extract date/time
            $dateTime = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
            
            // Extract GPS coordinates
            $gps = 'Location not available';
            if (isset($exif['GPSLatitude']) && isset($exif['GPSLongitude']) && 
                isset($exif['GPSLatitudeRef']) && isset($exif['GPSLongitudeRef'])) {
                
                $lat = $this->getGpsCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                $lon = $this->getGpsCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
                
                if ($lat !== '0.000000' && $lon !== '0.000000') {
                    $gps = "{$lat}, {$lon}";
                }
            }
            
            return [
                'date_time' => $dateTime,
                'gps' => $gps
            ];
            
        } catch (\Exception $e) {
            Log::error('EXIF extraction failed: ' . $e->getMessage());
            
            return [
                'date_time' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'gps' => 'Location not available'
            ];
        }
    }

    /**
     * Convert GPS coordinate from EXIF format to decimal
     */
    private function getGpsCoordinate($coordinate, $hemisphere)
    {
        if (!is_array($coordinate) || count($coordinate) != 3) {
            return '0.000000';
        }
        
        $degrees = $coordinate[0];
        $minutes = $coordinate[1];
        $seconds = $coordinate[2];
        
        $result = $degrees + ($minutes / 60) + ($seconds / 3600);
        
        if ($hemisphere == 'S' || $hemisphere == 'W') {
            $result = -$result;
        }
        
        return number_format($result, 6);
    }

    /**
     * Add watermark text to live photo
     */
    private function addLivePhotoWatermarkText($image, $text)
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        
        try {
            $fontPath = public_path('fonts/Montserrat-VariableFont_wght.ttf');
            
            // Calculate font size based on image size
            $fontSize = max(12, min($imageWidth / 30, $imageHeight / 30));
            
            // Position at bottom left corner
            $x = 15; // 15px from left edge
            $y = $imageHeight - 15; // 15px from bottom edge
            
            // Add background text for better readability (shadow effect)
            $image->text($text, $x + 2, $y + 2, function ($font) use ($fontSize, $fontPath) {
                $font->filename($fontPath);
                $font->size($fontSize);
                $font->color('rgba(0, 0, 0, 0.8)');
                $font->align('left');
                $font->valign('bottom');
            });
            
            // Add main watermark text
            $image->text($text, $x, $y, function ($font) use ($fontSize, $fontPath) {
                $font->filename($fontPath);
                $font->size($fontSize);
                $font->color('rgba(255, 255, 255, 0.9)');
                $font->align('left');
                $font->valign('bottom');
            });
            
        } catch (\Exception $e) {
            Log::error('Live photo text watermark failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete live photos from storage
     */
    private function deleteLivePhotos($answer)
    {
        try {
            if (!$answer->answer_value) {
                return;
            }
            
            // Handle single photo or multiple photos
            $photos = is_string($answer->answer_value) && str_starts_with($answer->answer_value, '[') 
                ? json_decode($answer->answer_value, true) 
                : [$answer->answer_value];
            
            foreach ($photos as $photo) {
                $filePath = is_array($photo) ? $photo['file_path'] : $photo;
                if ($filePath && Storage::disk('s3')->exists($filePath)) {
                    Storage::disk('s3')->delete($filePath);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Live photo deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint for frontend field calculations
     */
    public function calculateFields(Request $request)
    {
        try {
            $fieldValues = $request->input('field_values', []);
            $formVersionId = $request->input('form_version_id');
            
            if (!$formVersionId) {
                return response()->json(['success' => false, 'error' => 'Form version ID required'], 400);
            }
            
            $formVersion = \App\Models\FormVersion::findOrFail($formVersionId);
            $calculatedFields = $formVersion->fields()
                ->where('field_type', 'calculated')
                ->orderBy('order_position')
                ->get();
            
            if ($calculatedFields->isEmpty()) {
                return response()->json(['success' => true, 'calculated_values' => []]);
            }
            
            // Create a temporary submission object for calculations
            $tempSubmission = new FormSubmission([
                'form_version_id' => $formVersionId,
                'status' => 'draft'
            ]);
            
            // Mock answers for calculation
            $tempAnswers = collect();
            foreach ($fieldValues as $fieldCode => $value) {
                $field = $formVersion->fields()->where('field_code', $fieldCode)->first();
                if ($field) {
                    $tempAnswers->push(new \App\Models\FormAnswer([
                        'form_field_id' => $field->id,
                        'answer_value' => $value
                    ]));
                }
            }
            
            // Override the answers relationship for calculation
            $tempSubmission->setRelation('answers', $tempAnswers);
            $tempSubmission->setRelation('formVersion', $formVersion);
            
            $calculationService = app(\App\Services\CalculationService::class);
            $calculatedValues = [];
            
            foreach ($calculatedFields as $field) {
                // Create a custom submission object that can handle temporary answers
                $customSubmission = new class($tempSubmission, $tempAnswers, $formVersion) {
                    private $submission;
                    private $answers;
                    private $formVersion;
                    
                    public function __construct($submission, $answers, $formVersion) {
                        $this->submission = $submission;
                        $this->answers = $answers;
                        $this->formVersion = $formVersion;
                    }
                    
                    public function getAnswer($fieldCode) {
                        $field = $this->formVersion->fields()->where('field_code', $fieldCode)->first();
                        if (!$field) return null;
                        
                        $answer = $this->answers->firstWhere('form_field_id', $field->id);
                        return $answer ? $answer->answer_value : null;
                    }
                    
                    public function __get($property) {
                        return $this->submission->$property;
                    }
                    
                    public function __call($method, $args) {
                        return $this->submission->$method(...$args);
                    }
                };
                
                $calculatedValue = $calculationService->calculateFieldValue($field, $customSubmission);
                
                if ($calculatedValue !== null) {
                    $format = $field->validation_rules['format'] ?? 'number';
                    $formattedValue = $calculationService->formatValue($calculatedValue, $format);
                    $calculatedValues[$field->field_code] = $formattedValue;
                }
            }
            
            return response()->json([
                'success' => true,
                'calculated_values' => $calculatedValues
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Frontend calculation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Calculation failed'], 500);
        }
    }

    /**
     * Calculate date from validation rule
     */
    private function calculateDateFromRule($rule)
    {
        if (!$rule || !isset($rule['type'])) {
            return null;
        }

        switch ($rule['type']) {
            case 'fixed':
                return $rule['value'] ?? null;
            case 'today':
                return date('Y-m-d');
            case 'today_minus':
                $days = $rule['days'] ?? 0;
                return date('Y-m-d', strtotime("-{$days} days"));
            case 'today_plus':
                $days = $rule['days'] ?? 0;
                return date('Y-m-d', strtotime("+{$days} days"));
            default:
                return null;
        }
    }
}