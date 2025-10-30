@props(['submission', 'fields'])

<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="far fa-list-check"></i>
                Form Answers
            </h3>
            
            <!-- Search and Filter -->
            <div class="card-actions">
                <div class="input-group input-group-sm" style="max-width: 250px; width: 100%;">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search answers..." 
                           id="searchAnswers">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="far fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Desktop Table View -->
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table" id="answersTable">
                        <thead>
                            <tr>
                                <th style="width: 30%; min-width: 200px;">Question</th>
                                <th>Answer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fields as $field)
                                @php
                                    $answer = $submission->answers->where('form_field_id', $field->id)->first();
                                @endphp
                                <tr class="answer-row" data-field="{{ strtolower($field->field_label) }}">
                                    <td>
                                        <span class="{{ $field->is_required ? 'required' : '' }}">
                                            <strong>{{ $field->field_label }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <x-field-answer :field="$field" :answer="$answer" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Mobile Card View -->
            <div class="d-md-none">
                @foreach($fields as $field)
                    @php
                        $answer = $submission->answers->where('form_field_id', $field->id)->first();
                    @endphp
                    <div class="card mb-3 answer-card" data-field="{{ strtolower($field->field_label) }}">
                        <div class="card-header">
                            <h6 class="mb-0">
                                {{ $field->field_label }}
                                @if($field->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </h6>
                        </div>
                        <div class="card-body">
                            <x-field-answer :field="$field" :answer="$answer" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>