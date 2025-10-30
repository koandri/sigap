@extends('layouts.app')

@section('title', 'Create Form Request')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Create Form Request
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('form-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>
                        Back to Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if($formDocuments->count() > 0)
                <form method="POST" action="{{ route('form-requests.store') }}" id="formRequestForm">
                    @csrf
                    
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Add Forms to Request</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="form-label">Search and Select Form</label>
                                    <select id="form-select" class="form-select">
                                        <option value="">Type to search forms...</option>
                                        @foreach($formDocuments as $document)
                                            <option value="{{ $document->id }}" 
                                                    data-version-id="{{ $document->activeVersion->id }}"
                                                    data-number="{{ $document->document_number }}"
                                                    data-department="{{ $document->department->name }}">
                                                {{ $document->title }} ({{ $document->document_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" id="quantity-input" class="form-control" min="1" max="100" value="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-primary w-100" onclick="addFormToRequest()">
                                        <i class="far fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Selected Forms</h3>
                        </div>
                        <div class="card-body">
                            <div id="selected-forms-container">
                                <div class="empty" id="empty-state">
                                    <div class="empty-icon">
                                        <i class="far fa-inbox"></i>
                                    </div>
                                    <p class="empty-title">No forms selected</p>
                                    <p class="empty-subtitle text-muted">
                                        Use the form selector above to add forms to your request.
                                    </p>
                                </div>
                            </div>
                            <div id="forms-list" class="table-responsive" style="display: none;">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Form Title</th>
                                            <th>Document Number</th>
                                            <th>Department</th>
                                            <th width="120">Quantity</th>
                                            <th width="80">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected-forms-tbody">
                                        <!-- Dynamic rows will be added here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Total Forms:</strong> <span id="total-forms">0</span> |
                                    <strong>Total Quantity:</strong> <span id="total-quantity">0</span>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                        <i class="far fa-paper-plane"></i>
                                        Submit Request
                                    </button>
                                    <a href="{{ route('form-requests.index') }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="far fa-file-alt"></i>
                            </div>
                            <p class="empty-title">No form documents available</p>
                            <p class="empty-subtitle text-muted">
                                There are no form documents available for request.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
<script>
let selectedForms = [];
let formSelectInstance;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tom Select
    formSelectInstance = new TomSelect('#form-select', {
        maxOptions: null,
        placeholder: 'Type to search forms...',
        render: {
            option: function(data, escape) {
                if (!data.value) return '<div>' + escape(data.text) + '</div>';
                return '<div>' +
                    '<strong>' + escape(data.text.split('(')[0].trim()) + '</strong><br>' +
                    '<small class="text-muted">' + escape(data.number) + ' - ' + escape(data.department) + '</small>' +
                    '</div>';
            }
        }
    });
});

function addFormToRequest() {
    const selectElement = document.getElementById('form-select');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const quantity = parseInt(document.getElementById('quantity-input').value);
    
    if (!selectedOption.value) {
        alert('Please select a form');
        return;
    }
    
    if (quantity < 1 || quantity > 100) {
        alert('Quantity must be between 1 and 100');
        return;
    }
    
    const documentId = selectedOption.value;
    const versionId = selectedOption.dataset.versionId;
    const title = selectedOption.text.split('(')[0].trim();
    const number = selectedOption.dataset.number;
    const department = selectedOption.dataset.department;
    
    // Check if already added
    const existingIndex = selectedForms.findIndex(f => f.documentId === documentId);
    if (existingIndex !== -1) {
        // Update quantity instead
        selectedForms[existingIndex].quantity += quantity;
        updateFormRow(existingIndex);
    } else {
        // Add new form
        selectedForms.push({
            documentId: documentId,
            versionId: versionId,
            title: title,
            number: number,
            department: department,
            quantity: quantity
        });
        addFormRow(selectedForms.length - 1);
    }
    
    // Reset selection
    formSelectInstance.clear();
    document.getElementById('quantity-input').value = 1;
    
    updateTotals();
    updateUI();
}

function addFormRow(index) {
    const form = selectedForms[index];
    const tbody = document.getElementById('selected-forms-tbody');
    
    const row = document.createElement('tr');
    row.id = `form-row-${index}`;
    row.innerHTML = `
        <td>
            ${form.title}
            <input type="hidden" name="forms[${index}][document_version_id]" value="${form.versionId}">
        </td>
        <td>${form.number}</td>
        <td>${form.department}</td>
        <td>
            <input type="number" name="forms[${index}][quantity]" 
                   class="form-control form-control-sm" 
                   value="${form.quantity}" 
                   min="1" max="100" 
                   onchange="updateQuantity(${index}, this.value)">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeForm(${index})">
                <i class="far fa-trash-alt"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function updateFormRow(index) {
    const form = selectedForms[index];
    const row = document.getElementById(`form-row-${index}`);
    const quantityInput = row.querySelector('input[type="number"]');
    quantityInput.value = form.quantity;
}

function updateQuantity(index, newQuantity) {
    const quantity = parseInt(newQuantity);
    if (quantity < 1 || quantity > 100) {
        alert('Quantity must be between 1 and 100');
        const form = selectedForms[index];
        const row = document.getElementById(`form-row-${index}`);
        const quantityInput = row.querySelector('input[type="number"]');
        quantityInput.value = form.quantity;
        return;
    }
    selectedForms[index].quantity = quantity;
    updateTotals();
}

function removeForm(index) {
    selectedForms.splice(index, 1);
    rebuildFormsList();
    updateTotals();
    updateUI();
}

function rebuildFormsList() {
    const tbody = document.getElementById('selected-forms-tbody');
    tbody.innerHTML = '';
    selectedForms.forEach((form, index) => {
        addFormRow(index);
    });
}

function updateTotals() {
    const totalForms = selectedForms.length;
    const totalQuantity = selectedForms.reduce((sum, form) => sum + form.quantity, 0);
    
    document.getElementById('total-forms').textContent = totalForms;
    document.getElementById('total-quantity').textContent = totalQuantity;
}

function updateUI() {
    const hasForms = selectedForms.length > 0;
    document.getElementById('empty-state').style.display = hasForms ? 'none' : 'block';
    document.getElementById('forms-list').style.display = hasForms ? 'block' : 'none';
    document.getElementById('submit-btn').disabled = !hasForms;
}
</script>
@endpush
@endsection
