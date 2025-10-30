@extends('layouts.app')

@section('title', 'Form: ' . $form->name . ' v' . $version->version_number)

@push('styles')
<style>
    .sortable-handle {
        cursor: move;
        color: #6c757d;
    }
    .sortable-handle:hover {
        color: #212529;
    }
    .ui-sortable-helper {
        background: #fff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }
    .sortable-placeholder {
        background: #f8f9fa;
        visibility: visible !important;
    }
</style>
@endpush

@section('content')
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE HEADER -->
            <!-- BEGIN PAGE BODY -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        @include('layouts.alerts')
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Version Information
                                        @if($version->is_active)
                                        <span class="badge badge-outline text-success ms-2">Active Version</span>
                                        @endif
                                    </h3>
                                    <div class="card-actions">
                                        @if(!$version->is_active && $version->fields->count() > 0)
                                        <form action="{{ route('formversions.activate', [$form, $version]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success btn-2" onclick="return confirm('Activate this version?')">
                                                <i class="far fa-circle-check"></i>&nbsp;Activate Version
                                            </button>
                                        </form>
                                        @endif
                                        <a href="{{ route('formversions.index', $form) }}" class="btn btn-sm btn-2"><i class="far fa-arrow-left"></i>&nbsp;Back to Form Versions</a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Version Number:</th>
                                                    <td>v{{ $version->version_number }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Active?</th>
                                                    <td>{!! formatBoolean($version->is_active) !!}</td>
                                                </tr>
                                                <tr>
                                                    <th>Version Description:</th>
                                                    <td>{{ $version->description ?: '-' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Created By:</th>
                                                    <td>{{ $version->creator?->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Created On:</th>
                                                    <td>{{ $version->created_on->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Total Submissions:</th>
                                                    <td>{{ $version->submissions->count() }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Form Fields - {{ $version->fields->count() }} field(s)</h3>
                                    <div class="card-actions">
                                        @if($version->fields->count() > 0 && $version->submissions->count() == 0)
                                        <a class="btn btn-sm btn-secondary btn-2" href="#" id="toggleReorder"><i class="far fa-up-down-left-right"></i>&nbsp;Reorder Fields</a>
                                        @endif
                                        <a class="btn btn-sm btn-primary btn-2" href="#" onclick="addNewField();"><i class="far fa-input-text"></i>&nbsp;Add Fields</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($orderedFields->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="fieldsTable">
                                                <thead>
                                                    <tr>
                                                        <th width="5%" class="reorder-column" style="display: none;"><i class="far fa-arrows-up-down-left-right"></i></th>
                                                        <th width="5%">#</th>
                                                        <th width="20%">Field Code</th>
                                                        <th width="25%">Label</th>
                                                        <th width="15%">Type</th>
                                                        <th width="10%">Required</th>
                                                        <th width="10%">Options</th>
                                                        <th width="15%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sortableFields">
                                                    @foreach($orderedFields as $index => $field)
                                                    <tr data-field-id="{{ $field->id }}" data-order="{{ $field->order_position }}">
                                                        <td class="reorder-column" style="display: none;">
                                                            <span class="sortable-handle">
                                                                <i class="far fa-grip-vertical"></i>
                                                            </span>
                                                        </td>
                                                        <td class="field-number">{{ $index + 1 }}</td>
                                                        <td>
                                                            <code>{{ $field->field_code }}</code>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $field->field_label }}</strong>
                                                            @if($field->help_text)
                                                            <br>
                                                            <small class="text-muted">{{ Str::limit($field->help_text, 50) }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ \App\Models\FormField::FIELD_TYPES[$field->field_type] ?? $field->field_type }}</td>
                                                        <td>
                                                            @if($field->field_type === 'calculated')
                                                                <span class="badge badge-outline text-info">Calculated</span>
                                                            @elseif($field->field_type === 'hidden')
                                                                <span class="badge badge-outline text-secondary">Hidden</span>
                                                            @elseif($field->is_required)
                                                                <span class="badge badge-outline text-danger">Yes</span>
                                                            @else
                                                                <span class="badge badge-outline text-success">No</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $field->options->count() }}</td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" title="Edit Field" onclick="editField({{ $field->id }})"><i class="far fa-pen-to-square"></i></button>
                                                                @if($field->hasOptions())
                                                                <button class="btn btn-outline-secondary" title="Manage Options" onclick="manageOptions({{ $field->id }})"><i class="far fa-list-ul"></i></button>
                                                                @endif
                                                                @if($version->submissions->count() == 0)
                                                                <button class="btn btn-outline-danger" title="Delete Field" onclick="deleteField({{ $field->id }})"><i class="far fa-trash-can"></i></button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div id="reorderActions" class="alert alert-info mt-3" style="display: none;">
                                            <i class="far fa-circle-info"></i> &nbsp;
                                            Drag and drop rows to reorder fields. 
                                            <button class="btn btn-sm btn-success ms-2" onclick="saveOrder()">
                                                <i class="far fa-floppy-disk"></i> &nbsp;Save Order
                                            </button>
                                            <button class="btn btn-sm btn-secondary" onclick="cancelReorder()">
                                                <i class="far fa-circle-xmark"></i> &nbsp;Cancel
                                            </button>
                                        </div>

                                        @if($version->submissions->count() > 0)
                                        <div class="alert alert-warning mt-3">
                                            <i class="fa-sharp fa-solid fa-triangle-exclamation"></i>
                                            This version has <strong>{{ $version->submissions->count() }}</strong> submission(s). 
                                            Fields cannot be deleted, but you can still edit labels and settings.
                                        </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4">
                                            <p class="text-muted mb-3">No fields added yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Form Preview -->
                        @if($orderedFields->count() > 0)
                        <div class="col-12">
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h3 class="card-title">Form Preview</h3>
                                    <div class="card-actions">
                                        <a class="btn btn-sm btn-primary" href="#" onclick="togglePreview(event)"><i class="far fa-eye"></i> &nbsp;Toggle Preview</a>
                                    </div>
                                </div>
                                <div class="card-body" id="formPreview" style="display: none;">
                                    @foreach($orderedFields as $field)
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label  {{ $field->is_required ? 'required' : '' }}">{{ $field->field_label }}</label>
                                        <div class="col-sm-10">
                                            @switch($field->field_type)
                                                @case('text_short')
                                                    <input type="text" class="form-control" placeholder="{{ $field->placeholder }}" disabled>
                                                    @break
                                                
                                                @case('text_long')
                                                    <textarea class="form-control" rows="3" placeholder="{{ $field->placeholder }}" disabled></textarea>
                                                    @break
                                                
                                                @case('number')
                                                    <input type="number" class="form-control" disabled>
                                                    @break
                                                
                                                @case('date')
                                                    @php
                                                        $minDate = '';
                                                        $maxDate = '';
                                                        $dateInfo = [];
                                                        
                                                        if ($field->validation_rules) {
                                                            $rules = $field->validation_rules;
                                                            
                                                            // Calculate min date
                                                            if (isset($rules['date_min'])) {
                                                                $minRule = $rules['date_min'];
                                                                if ($minRule['type'] === 'fixed') {
                                                                    $minDate = $minRule['value'];
                                                                    $dateInfo[] = 'From: ' . date('d M Y', strtotime($minRule['value']));
                                                                } elseif ($minRule['type'] === 'today') {
                                                                    $minDate = date('Y-m-d');
                                                                    $dateInfo[] = 'From: Today';
                                                                } elseif ($minRule['type'] === 'today_minus') {
                                                                    $minDate = date('Y-m-d', strtotime('-' . ($minRule['days'] ?? 0) . ' days'));
                                                                    $dateInfo[] = 'From: Today -' . $minRule['days'] . ' days';
                                                                } elseif ($minRule['type'] === 'today_plus') {
                                                                    $minDate = date('Y-m-d', strtotime('+' . ($minRule['days'] ?? 0) . ' days'));
                                                                    $dateInfo[] = 'From: Today +' . $minRule['days'] . ' days';
                                                                }
                                                            }
                                                            
                                                            // Calculate max date
                                                            if (isset($rules['date_max'])) {
                                                                $maxRule = $rules['date_max'];
                                                                if ($maxRule['type'] === 'fixed') {
                                                                    $maxDate = $maxRule['value'];
                                                                    $dateInfo[] = 'To: ' . date('d M Y', strtotime($maxRule['value']));
                                                                } elseif ($maxRule['type'] === 'today') {
                                                                    $maxDate = date('Y-m-d');
                                                                    $dateInfo[] = 'To: Today';
                                                                } elseif ($maxRule['type'] === 'today_minus') {
                                                                    $maxDate = date('Y-m-d', strtotime('-' . ($maxRule['days'] ?? 0) . ' days'));
                                                                    $dateInfo[] = 'To: Today -' . $maxRule['days'] . ' days';
                                                                } elseif ($maxRule['type'] === 'today_plus') {
                                                                    $maxDate = date('Y-m-d', strtotime('+' . ($maxRule['days'] ?? 0) . ' days'));
                                                                    $dateInfo[] = 'To: Today +' . $maxRule['days'] . ' days';
                                                                }
                                                            }
                                                            
                                                            // Allowed days
                                                            if (isset($rules['allowed_days']) && count($rules['allowed_days']) < 7) {
                                                                $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                                                $allowed = array_map(function($d) use ($days) {
                                                                    return $days[intval($d)];
                                                                }, $rules['allowed_days']);
                                                                $dateInfo[] = 'Days: ' . implode(', ', $allowed);
                                                            }
                                                            
                                                            // Disabled dates count
                                                            if (isset($rules['disabled_dates']) && count($rules['disabled_dates']) > 0) {
                                                                $dateInfo[] = count($rules['disabled_dates']) . ' dates disabled';
                                                            }
                                                        }
                                                    @endphp
                                                    <input type="date" class="form-control" {{ $minDate ? 'min=' . $minDate : '' }} {{ $maxDate ? 'max=' . $maxDate : '' }} disabled>
                                                    @if(!empty($dateInfo))
                                                        <small class="text-info">
                                                            <i class="far fa-circle-info"></i> &nbsp;{{ implode(' | ', $dateInfo) }}
                                                        </small>
                                                    @endif
                                                    @break
                                                
                                                @case('datetime')
                                                    @php
                                                        $minDateTime = '';
                                                        $maxDateTime = '';
                                                        $dateTimeInfo = [];
                                                        
                                                        if ($field->validation_rules) {
                                                            $rules = $field->validation_rules;
                                                            
                                                            // Calculate min datetime
                                                            if (isset($rules['date_min'])) {
                                                                $minRule = $rules['date_min'];
                                                                if ($minRule['type'] === 'fixed') {
                                                                    $minDateTime = $minRule['value'] . 'T00:00';
                                                                    $dateTimeInfo[] = 'From: ' . date('d M Y', strtotime($minRule['value']));
                                                                } elseif ($minRule['type'] === 'today') {
                                                                    $minDateTime = date('Y-m-d\T00:00');
                                                                    $dateTimeInfo[] = 'From: Today';
                                                                } elseif ($minRule['type'] === 'today_minus') {
                                                                    $minDateTime = date('Y-m-d\T00:00', strtotime('-' . ($minRule['days'] ?? 0) . ' days'));
                                                                    $dateTimeInfo[] = 'From: Today -' . $minRule['days'] . ' days';
                                                                } elseif ($minRule['type'] === 'today_plus') {
                                                                    $minDateTime = date('Y-m-d\T00:00', strtotime('+' . ($minRule['days'] ?? 0) . ' days'));
                                                                    $dateTimeInfo[] = 'From: Today +' . $minRule['days'] . ' days';
                                                                }
                                                            }
                                                            
                                                            // Calculate max datetime
                                                            if (isset($rules['date_max'])) {
                                                                $maxRule = $rules['date_max'];
                                                                if ($maxRule['type'] === 'fixed') {
                                                                    $maxDateTime = $maxRule['value'] . 'T23:59';
                                                                    $dateTimeInfo[] = 'To: ' . date('d M Y', strtotime($maxRule['value']));
                                                                } elseif ($maxRule['type'] === 'today') {
                                                                    $maxDateTime = date('Y-m-d\T23:59');
                                                                    $dateTimeInfo[] = 'To: Today';
                                                                } elseif ($maxRule['type'] === 'today_minus') {
                                                                    $maxDateTime = date('Y-m-d\T23:59', strtotime('-' . ($maxRule['days'] ?? 0) . ' days'));
                                                                    $dateTimeInfo[] = 'To: Today -' . $maxRule['days'] . ' days';
                                                                } elseif ($maxRule['type'] === 'today_plus') {
                                                                    $maxDateTime = date('Y-m-d\T23:59', strtotime('+' . ($maxRule['days'] ?? 0) . ' days'));
                                                                    $dateTimeInfo[] = 'To: Today +' . $maxRule['days'] . ' days';
                                                                }
                                                            }
                                                            
                                                            // Allowed days
                                                            if (isset($rules['allowed_days']) && count($rules['allowed_days']) < 7) {
                                                                $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                                                $allowed = array_map(function($d) use ($days) {
                                                                    return $days[intval($d)];
                                                                }, $rules['allowed_days']);
                                                                $dateTimeInfo[] = 'Days: ' . implode(', ', $allowed);
                                                            }
                                                        }
                                                    @endphp
                                                    <input type="datetime-local" 
                                                        class="form-control" 
                                                        {{ $minDateTime ? 'min=' . $minDateTime : '' }}
                                                        {{ $maxDateTime ? 'max=' . $maxDateTime : '' }}
                                                        disabled>
                                                    @if(!empty($dateTimeInfo))
                                                        <small class="text-info">
                                                            <i class="far fa-circle-info"></i> &nbsp;{{ implode(' | ', $dateTimeInfo) }}
                                                        </small>
                                                    @endif
                                                    @break
                                                
                                                @case('select_single')
                                                    <select class="form-select" disabled>
                                                        <option>-- Select --</option>
                                                        @foreach($field->options as $option)
                                                            <option>{{ $option->option_label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @break

                                                @case('select_multiple')
                                                    <select class="form-select" multiple disabled>
                                                        <option>-- Select --</option>
                                                        @foreach($field->options as $option)
                                                            <option>{{ $option->option_label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @break
                                                
                                                @case('radio')
                                                    @foreach($field->options as $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" disabled>
                                                        <label class="form-check-label">{{ $option->option_label }}</label>
                                                    </div>
                                                    @endforeach
                                                    @break
                                                
                                                @case('checkbox')
                                                    @foreach($field->options as $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                        <label class="form-check-label">{{ $option->option_label }}</label>
                                                    </div>
                                                    @endforeach
                                                    @break
                                                
                                                @case('boolean')
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                        <label class="form-check-label">Yes</label>
                                                    </div>
                                                    @break
                                                
                                                @case('file')
                                                    <input type="file" class="form-control" disabled>
                                                    @break

                                                @case('calculated')
                                                    @php
                                                        $calculationRules = $field->validation_rules ?? [];
                                                        $format = $calculationRules['format'] ?? 'number';
                                                    @endphp
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" value="(Calculated)" style="background-color: #e9ecef; font-style: italic;" disabled>
                                                        <span class="input-group-text">
                                                            <i class="far fa-calculator text-primary"></i>
                                                        </span>
                                                    </div>
                                                    <small class="form-text text-info">
                                                        <i class="far fa-circle-info"></i>
                                                        Formula: <code>{{ $field->calculation_formula }}</code>
                                                        <br>
                                                        Format: {{ ucfirst(str_replace('_', ' ', $format)) }}
                                                    </small>
                                                    @break

                                                @case('hidden')
                                                    <!-- Hidden field preview -->
                                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" value="(Hidden Field)" style="background-color: #f8f9fa; font-style: italic; border-style: dashed;" disabled>
                                                            <span class="input-group-text">
                                                                <i class="far fa-eye-slash text-muted"></i>
                                                            </span>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            <i class="far fa-circle-info"></i>
                                                            Default: {{ $field->validation_rules['default_value'] ?? 'Not set' }}
                                                            @if(isset($field->validation_rules['value_type']) && $field->validation_rules['value_type'] === 'dynamic')
                                                                (Dynamic: {{ $field->validation_rules['dynamic_type'] ?? 'Unknown' }})
                                                            @endif
                                                        </small>
                                                    @else
                                                        <!-- Don't show hidden fields to regular users in preview -->
                                                        <div class="text-muted fst-italic">
                                                            <i class="far fa-eye-slash"></i>&nbsp;Hidden field (not visible to users)
                                                        </div>
                                                    @endif
                                                    @break

                                                @case('signature')
                                                    @php
                                                        $signatureRules = $field->validation_rules ?? [];
                                                        $width = $signatureRules['width'] ?? 400;
                                                        $height = $signatureRules['height'] ?? 200;
                                                        $penColor = $signatureRules['pen_color'] ?? '#000000';
                                                        $backgroundColor = $signatureRules['background_color'] ?? '#ffffff';
                                                    @endphp
                                                    
                                                    <div class="border rounded p-3" style="background: #f8f9fa;">
                                                        <div class="text-center mb-2">
                                                            <i class="far fa-signature" style="font-size: 2rem;"></i>
                                                            <p class="mb-0 mt-2">
                                                                <strong>Digital Signature Pad</strong>
                                                            </p>
                                                            <small class="text-muted">
                                                                Users can draw their signature using mouse or touch
                                                            </small>
                                                        </div>
                                                        
                                                        <!-- Preview signature pad -->
                                                        <div class="signature-preview-container text-center">
                                                            <div class="border rounded d-inline-block p-2" 
                                                                style="background: {{ $backgroundColor }}; border-style: dashed !important;">
                                                                <div style="width: {{ min($width, 300) }}px; height: {{ min($height, 150) }}px; display: flex; align-items: center; justify-content: center;">
                                                                    <div class="text-center">
                                                                        <i class="far fa-pencil" style="font-size: 1.5rem; color: {{ $penColor }};"></i>
                                                                        <br>
                                                                        <small class="text-muted">{{ $width }}×{{ $height }}px</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        @if($field->help_text)
                                                            <div class="mt-2 text-center">
                                                                <small class="text-muted">{{ $field->help_text }}</small>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Configuration Info -->
                                                        <div class="mt-2 text-center">
                                                            <small class="text-info">
                                                                <i class="far fa-gear"></i>&nbsp;
                                                                Pen: {{ $penColor }} | Background: {{ $backgroundColor }}
                                                                @if($signatureRules['required_draw'] ?? false)
                                                                    | Drawing Required
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                    @break
                                                
                                                @default
                                                    <input type="text" class="form-control" disabled>
                                            @endswitch
                                            @if($field->help_text)
                                            <small class="form-hint">{{ $field->help_text }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="col-12">
                            <div class="card mt-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            @if($version->fields->count() == 0)
                                                <span class="text-muted">Add fields to enable form submission</span>
                                            @elseif(!$version->is_active)
                                                <span class="text-warning">
                                                    <i class="far fa-circle-exclamation"></i>
                                                    Activate this version to enable form submission
                                                </span>
                                            @else
                                                <span class="text-success">
                                                    <i class="far fa-circle-check"></i>
                                                    This version is active and ready for submissions
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            @if($version->is_active && $version->fields->count() > 0)
                                                <button onclick="testFormSubmission()" class="btn btn-primary btn-sm">
                                                    <i class="far fa-arrow-right"></i>&nbsp;Test Form Submission
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection

@push('scripts')
<!-- Add jQuery UI for sortable -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="/assets/js/signature_pad.umd.min.js"></script>

<script>
    let reorderMode = false;
    let originalOrder = [];

    // Toggle reorder mode
    document.getElementById('toggleReorder')?.addEventListener('click', function() {
        if (!reorderMode) {
            enableReorderMode();
        } else {
            cancelReorder();
        }
    });

    function enableReorderMode() {
        reorderMode = true;
        
        // Show reorder column and actions
        document.querySelectorAll('.reorder-column').forEach(el => el.style.display = '');
        document.getElementById('reorderActions').style.display = 'block';
        document.getElementById('toggleReorder').innerHTML = '<i class="far fa-circle-x"></i> &nbsp;Cancel Reorder';
        
        // Store original order
        originalOrder = [];
        document.querySelectorAll('#sortableFields tr').forEach((row, index) => {
            originalOrder.push({
                id: row.dataset.fieldId,
                order: row.dataset.order
            });
        });
        
        // Enable sortable
        $('#sortableFields').sortable({
            handle: '.sortable-handle',
            placeholder: 'sortable-placeholder',
            helper: function(e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function(index) {
                    $(this).width($originals.eq(index).width());
                });
                return $helper;
            },
            update: function(event, ui) {
                updateFieldNumbers();
            }
        }).disableSelection();
    }

    function cancelReorder() {
        reorderMode = false;
        
        // Hide reorder column and actions
        document.querySelectorAll('.reorder-column').forEach(el => el.style.display = 'none');
        document.getElementById('reorderActions').style.display = 'none';
        document.getElementById('toggleReorder').innerHTML = '<i class="far fa-up-down-left-right"></i> &nbsp;Reorder Fields';
        
        // Disable sortable
        if ($('#sortableFields').sortable('instance')) {
            $('#sortableFields').sortable('destroy');
        }
        
        // Restore original order if needed
        location.reload();
    }

    function updateFieldNumbers() {
        document.querySelectorAll('#sortableFields tr').forEach((row, index) => {
            row.querySelector('.field-number').textContent = index + 1;
        });
    }

    function saveOrder() {
        const fields = [];
        document.querySelectorAll('#sortableFields tr').forEach((row, index) => {
            fields.push({
                id: row.dataset.fieldId,
                order: (index + 1) * 10
            });
        });
        
        // Send AJAX request
        const url = '/forms/{{ $form->id }}/versions/{{ $version->id }}/fields/reorder';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ fields: fields })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Fields reordered successfully!');
                location.reload();
            } else {
                alert('Failed to reorder fields: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error reordering fields');
        });
    }
    function addNewField() {
        window.location.href = "{{ route('formfields.create', [$form, $version]) }}";
    }

    function editField(fieldId) {
        // Using template literal with Laravel route
        window.location.href = "{{ route('formfields.edit', [$form, $version, '__FIELD_ID__']) }}".replace('__FIELD_ID__', fieldId);
    }

    function manageOptions(fieldId) {
        // Using template literal with Laravel route
        window.location.href = "{{ route('formfields.options', [$form, $version, '__FIELD_ID__']) }}".replace('__FIELD_ID__', fieldId);
    }

    function deleteField(fieldId) {
        if(confirm('Are you sure you want to delete this field? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('formfields.destroy', [$form, $version, '__FIELD_ID__']) }}".replace('__FIELD_ID__', fieldId);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function togglePreview(event) {
        const preview = document.getElementById('formPreview');
        if (!preview) {
            return;
        }
        
        // Find the button that triggered the event
        const button = event ? event.target.closest('a') : document.querySelector('a[onclick*="togglePreview"]');
        if (!button) {
            return;
        }
        
        if (preview.style.display === 'none' || preview.style.display === '') {
            preview.style.display = 'block';
            button.innerHTML = '<i class="far fa-eye-slash"></i> &nbsp;Hide Preview';
            // Smooth scroll to preview
            setTimeout(() => {
                preview.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            preview.style.display = 'none';
            button.innerHTML = '<i class="far fa-eye"></i> &nbsp;Toggle Preview';
        }
    }

    // Additional helper functions
    function testFormSubmission() {
        @if($version->is_active)
        window.location.href = "{{ route('formsubmissions.create', $form) }}";
        @else
        alert('Please activate this version first before testing submission.');
        @endif
    }

    // Initialize tooltips if using Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush