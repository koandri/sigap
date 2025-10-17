@extends('layouts.app')

@section('title', 'Import Items from Excel')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Import Items from Excel
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.items.index') }}" class="btn btn-outline-secondary d-none d-sm-inline-block">
                        <i class="fa-regular fa-arrow-left"></i>
                        View Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        @if(session('import_stats'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Import Statistics</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="h2 text-green">{{ session('import_stats')['categories_created'] }}</div>
                                        <div class="text-muted">Categories Created</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="h2 text-blue">{{ session('import_stats')['categories_skipped'] }}</div>
                                        <div class="text-muted">Categories Skipped</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="h2 text-green">{{ session('import_stats')['items_created'] }}</div>
                                        <div class="text-muted">Items Created</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="h2 text-orange">{{ session('import_stats')['items_updated'] ?? 0 }}</div>
                                        <div class="text-muted">Items Updated</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty(session('import_stats')['errors']))
                        <div class="mt-3">
                            <h4 class="text-red">Errors Encountered:</h4>
                            <ul class="text-muted">
                                @foreach(array_slice(session('import_stats')['errors'], 0, 10) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                @if(count(session('import_stats')['errors']) > 10)
                                    <li><em>... and {{ count(session('import_stats')['errors']) - 10 }} more errors</em></li>
                                @endif
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <div class="row row-deck row-cards">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Upload Excel File</h3>
                    </div>
                    <form method="POST" action="{{ route('manufacturing.items.import.process') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Excel File</label>
                                <input type="file" class="form-control @error('excel_file') is-invalid @enderror" 
                                       name="excel_file" accept=".xlsx,.xls" required>
                                @error('excel_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Upload your Excel file containing item data. Maximum file size: 10MB</small>
                            </div>
                            
                            <div class="alert alert-info" role="alert">
                                <h4 class="alert-title">Excel Format Requirements</h4>
                                <div class="text-muted">
                                    The Excel file should contain the following columns:
                                    <ul class="mt-2">
                                        <li><strong>Column B:</strong> Kategori Barang (Item Category)</li>
                                        <li><strong>Column C:</strong> Kode Barang (Accurate ID)</li>
                                        <li><strong>Column D:</strong> Nama Barang (Item Name)</li>
                                        <li><strong>Column BE:</strong> Merk (Brand/Manufacturer)</li>
                                        <li><strong>Column F:</strong> Satuan (Unit)</li>
                                        <li><strong>Column BF:</strong> Nama Singkat (Short Name)</li>
                                    </ul>
                                    <p class="mt-2 mb-0">
                                        <strong>Note:</strong> New categories and items will be created. 
                                        Existing items (based on Accurate ID) will be updated with new data from Excel.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('manufacturing.dashboard') }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="fa-regular fa-file-import me-2"></i>
                                    Import Excel File
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Import Process</h3>
                    </div>
                    <div class="card-body">
                        <div class="steps steps-vertical">
                            <div class="step-item">
                                <div class="h4 m-0">Step 1: Upload File</div>
                                <div class="text-muted">Select and upload your Excel file containing item data from your accounting system.</div>
                            </div>
                            <div class="step-item">
                                <div class="h4 m-0">Step 2: Validation</div>
                                <div class="text-muted">The system will validate the file format and check for required columns.</div>
                            </div>
                            <div class="step-item">
                                <div class="h4 m-0">Step 3: Category Import</div>
                                <div class="text-muted">New item categories will be created automatically from the "Kategori Barang" column.</div>
                            </div>
                            <div class="step-item">
                                <div class="h4 m-0">Step 4: Item Import</div>
                                <div class="text-muted">Items will be imported with their categories, using default values for missing fields.</div>
                            </div>
                            <div class="step-item">
                                <div class="h4 m-0">Step 5: Completion</div>
                                <div class="text-muted">You'll receive a detailed report showing what was imported and any errors encountered.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
